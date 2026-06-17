<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Services\BookingService;
use App\Services\CourtService;
use App\Exceptions\SlotNotAvailableException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GuestBookingController extends Controller
{
    protected $bookingService;
    protected $courtService;

    public function __construct(BookingService $bookingService, CourtService $courtService)
    {
        $this->bookingService = $bookingService;
        $this->courtService = $courtService;
    }

    /**
     * Show booking form for guest/customer.
     */
    public function showForm()
    {
        $courts = $this->courtService->listCourts(['status' => 'active']);

        return Inertia::render('Public/Booking', [
            'courts' => $courts
        ]);
    }

    /**
     * Store new booking (Guest / Login Customer).
     */
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        
        // Associate with logged-in user if available
        $data['user_id'] = Auth::check() ? Auth::id() : null;

        try {
            $booking = $this->bookingService->createBooking($data);
            
            // Redirect to a success page
            return redirect()->route('booking.success', $booking->booking_number)->with([
                'success' => 'Pemesanan berhasil dikonfirmasi!',
                'booking_number' => $booking->booking_number,
                'total_price' => $booking->total_price,
            ]);
        } catch (SlotNotAvailableException $e) {
            return redirect()->back()->withErrors([
                'slot_conflict' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show booking success page.
     */
    public function success(string $bookingNumber)
    {
        // Fetch booking with court relation loaded
        $booking = $this->bookingService->getBookingByNumber($bookingNumber);

        if (!$booking) {
            abort(404, 'Booking tidak ditemukan.');
        }

        return Inertia::render('Public/BookingSuccess', [
            'booking' => $booking
        ]);
    }

    /**
     * Show tracking form for guest bookings.
     */
    public function showTrackForm()
    {
        return Inertia::render('Public/BookingTrack');
    }

    /**
     * Track booking by booking number.
     */
    public function track(Request $request)
    {
        $request->validate([
            'booking_number' => ['required', 'string']
        ]);

        $booking = $this->bookingService->getBookingByNumber($request->input('booking_number'));

        return Inertia::render('Public/BookingTrack', [
            'booking' => $booking,
            'searched' => true
        ]);
    }
}
