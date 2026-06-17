<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display list of bookings for the authenticated customer.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status']);
        $bookings = $this->bookingService->getCustomerHistory(Auth::id(), $filters);

        return Inertia::render('Customer/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $filters
        ]);
    }

    /**
     * Cancel a booking by the customer.
     */
    public function cancel(int $id)
    {
        $booking = $this->bookingService->getBookingById($id);

        // Security check: ensure this booking belongs to the authenticated user
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Aksi tidak diizinkan.');
        }

        if ($booking->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Booking yang sudah diproses tidak bisa dibatalkan.');
        }

        $this->bookingService->cancelBooking($id, 'Dibatalkan oleh Pelanggan', Auth::id());

        return redirect()->back()->with('success', 'Booking berhasil dibatalkan.');
    }
}
