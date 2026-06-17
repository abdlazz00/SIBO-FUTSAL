<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\RescheduleBookingRequest;
use App\Http\Requests\CancelBookingRequest;
use App\Services\BookingService;
use App\Services\CourtService;
use App\Exceptions\SlotNotAvailableException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    protected $bookingService;
    protected $courtService;

    public function __construct(BookingService $bookingService, CourtService $courtService)
    {
        $this->bookingService = $bookingService;
        $this->courtService = $courtService;
    }

    /**
     * Display a listing of bookings with search, status, and date filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'date']);
        $bookings = $this->bookingService->listBookings($filters);
        $courts = $this->courtService->listCourts(['status' => 'active']);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'courts' => $courts,
            'filters' => $filters
        ]);
    }

    /**
     * Store a manually input walk-in booking by Admin.
     */
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        
        // Mark as manual/walk-in booking
        $data['is_manual'] = true;
        $data['created_by'] = Auth::id();

        try {
            $this->bookingService->createBooking($data);
            return redirect()->back()->with('success', 'Booking manual berhasil ditambahkan.');
        } catch (SlotNotAvailableException $e) {
            return redirect()->back()->withErrors([
                'slot_conflict' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reschedule an active booking to a new date/time range.
     */
    public function reschedule(RescheduleBookingRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $this->bookingService->rescheduleBooking($id, $data['date'], $data['start_time'], $data['end_time']);
            return redirect()->back()->with('success', 'Jadwal booking berhasil diubah.');
        } catch (SlotNotAvailableException $e) {
            return redirect()->back()->withErrors([
                'slot_conflict' => $e->getMessage()
            ]);
        }
    }

    /**
     * Cancel booking with mandatory reason (min 10 chars, enforced by Request).
     */
    public function cancel(CancelBookingRequest $request, int $id)
    {
        $data = $request->validated();

        $this->bookingService->cancelBooking($id, $data['cancel_reason'], Auth::id());

        return redirect()->back()->with('success', 'Booking berhasil dibatalkan.');
    }

    /**
     * Export stub (will be fully implemented in Sprint 4).
     */
    public function export()
    {
        return redirect()->back()->with('info', 'Fitur ekspor laporan akan diaktifkan pada Sprint 4.');
    }
}
