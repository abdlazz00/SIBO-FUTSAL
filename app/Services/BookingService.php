<?php

namespace App\Services;

use App\Models\Court;
use App\Models\Booking;
use App\Models\CourtPriceOverride;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Exceptions\SlotNotAvailableException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingService
{
    protected $bookingRepository;
    protected $courtService;
    protected $notificationService;

    public function __construct(
        BookingRepositoryInterface $bookingRepository, 
        CourtService $courtService,
        NotificationService $notificationService
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->courtService = $courtService;
        $this->notificationService = $notificationService;
    }

    public function listBookings(array $filters = [])
    {
        return $this->bookingRepository->getAll($filters);
    }

    public function getBookingById(int $id)
    {
        return $this->bookingRepository->findById($id);
    }

    public function getBookingByNumber(string $bookingNumber)
    {
        return $this->bookingRepository->findByNumber($bookingNumber);
    }

    public function getCustomerHistory(int $userId, array $filters = [])
    {
        return $this->bookingRepository->getByUserId($userId, $filters);
    }

    /**
     * Generate booking number in format: VF-XXXXXX (6 alphanumeric uppercase characters)
     */
    public function generateBookingNumber(): string
    {
        do {
            // Generate a 6-character random uppercase alphanumeric string
            $code = 'VF-' . strtoupper(Str::random(6));
        } while (Booking::where('booking_number', $code)->exists());

        return $code;
    }

    /**
     * Get all slots for a court and date with booking availability status.
     */
    public function getAvailableSlots(int $courtId, string $date): array
    {
        $court = Court::findOrFail($courtId);
        
        // 1. Generate all possible slots for this court and date
        $allSlots = $this->courtService->generateSlots($court, $date);

        // 2. Fetch all active bookings for this court and date
        $activeBookings = Booking::where('court_id', $courtId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->get();

        // 3. Mark availability for each slot
        return array_map(function ($slot) use ($activeBookings) {
            $isBooked = false;
            $bookingDetails = null;

            foreach ($activeBookings as $booking) {
                // If the slot falls inside a booking range
                $slotStart = $slot['start_time'];
                $slotEnd = $slot['end_time'];

                // Overlap: slotStart < bookingEnd AND slotEnd > bookingStart
                if ($slotStart < $booking->end_time && $slotEnd > $booking->start_time) {
                    $isBooked = true;
                    $bookingDetails = [
                        'id' => $booking->id,
                        'booking_number' => $booking->booking_number,
                        'customer_name' => $booking->customer_name,
                        'status' => $booking->status,
                    ];
                    break;
                }
            }

            return array_merge($slot, [
                'status' => $isBooked ? 'booked' : 'available',
                'booking' => $bookingDetails,
            ]);
        }, $allSlots);
    }

    /**
     * Create a new booking with SELECT FOR UPDATE database locking.
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $courtId = $data['court_id'];
            $date = $data['date'];
            $startTime = $data['start_time'];
            $endTime = $data['end_time'];

            // 1. Pessimistic Lock the Court record for update to block concurrent checks
            Court::where('id', $courtId)->lockForUpdate()->firstOrFail();

            // 2. Check for conflicts
            $conflict = $this->bookingRepository->checkConflict($courtId, $date, $startTime, $endTime);
            if ($conflict) {
                throw new SlotNotAvailableException('Maaf, slot waktu ini baru saja dipesan oleh pengguna lain.');
            }

            // 3. Calculate final price (check for override)
            $court = Court::findOrFail($courtId);
            $override = CourtPriceOverride::where('court_id', $courtId)
                ->whereDate('date', $date)
                ->first();

            $pricePerSlot = $override ? (float) $override->price : (float) $court->price;

            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);
            $diffMinutes = $start->diffInMinutes($end);
            $numSlots = max(1, ceil($diffMinutes / $court->slot_duration));
            $totalPrice = $pricePerSlot * $numSlots;

            // 4. Generate unique booking number
            $bookingNumber = $this->generateBookingNumber();

            // 5. Create booking
            $bookingData = array_merge($data, [
                'booking_number' => $bookingNumber,
                'total_price' => $totalPrice,
                'status' => 'confirmed', // D-01: Booking otomatis berstatus Confirmed
            ]);

            $booking = $this->bookingRepository->create($bookingData);
            
            // Dispatch notification
            $booking->load('court');
            $this->notificationService->notifyBookingCreated($booking);

            return $booking;
        });
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(int $id, string $reason, ?int $cancelledByUserId = null): Booking
    {
        $booking = $this->bookingRepository->findById($id);

        if ($booking->status === 'cancelled') {
            return $booking;
        }

        $updatedBooking = $this->bookingRepository->update($id, [
            'status' => 'cancelled',
            'cancel_reason' => $reason,
            'cancelled_by' => $cancelledByUserId,
        ]);

        $updatedBooking->load('court');
        $this->notificationService->notifyBookingCancelled($updatedBooking, $reason);

        return $updatedBooking;
    }

    /**
     * Reschedule a booking (Admin-only).
     */
    public function rescheduleBooking(int $id, string $newDate, string $newStartTime, string $newEndTime): Booking
    {
        return DB::transaction(function () use ($id, $newDate, $newStartTime, $newEndTime) {
            $booking = $this->bookingRepository->findById($id);

            // 1. Lock Court
            Court::where('id', $booking->court_id)->lockForUpdate()->firstOrFail();

            // 2. Check for conflicts excluding current booking
            $conflict = $this->bookingRepository->checkConflict(
                $booking->court_id,
                $newDate,
                $newStartTime,
                $newEndTime,
                $id
            );

            if ($conflict) {
                throw new SlotNotAvailableException('Maaf, slot waktu baru tersebut sudah terisi.');
            }

            // 3. Recalculate price for new date/time
            $court = Court::findOrFail($booking->court_id);
            $override = CourtPriceOverride::where('court_id', $booking->court_id)
                ->whereDate('date', $newDate)
                ->first();

            $pricePerSlot = $override ? (float) $override->price : (float) $court->price;

            $start = Carbon::parse($newStartTime);
            $end = Carbon::parse($newEndTime);
            $diffMinutes = $start->diffInMinutes($end);
            $numSlots = max(1, ceil($diffMinutes / $court->slot_duration));
            $totalPrice = $pricePerSlot * $numSlots;

            // 4. Update booking
            $updatedBooking = $this->bookingRepository->update($id, [
                'date' => $newDate,
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
                'total_price' => $totalPrice,
            ]);

            $updatedBooking->load('court');
            $this->notificationService->notifyBookingRescheduled($updatedBooking);

            return $updatedBooking;
        });
    }
}
