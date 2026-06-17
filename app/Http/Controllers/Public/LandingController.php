<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CourtService;
use App\Services\BookingService;
use App\Repositories\Contracts\TestimonialRepositoryInterface;
use App\Models\Court;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    protected $courtService;
    protected $testimonialRepo;
    protected $bookingService;

    public function __construct(
        CourtService $courtService,
        TestimonialRepositoryInterface $testimonialRepo,
        BookingService $bookingService
    ) {
        $this->courtService = $courtService;
        $this->testimonialRepo = $testimonialRepo;
        $this->bookingService = $bookingService;
    }

    /**
     * Render landing page with active courts and testimonials.
     */
    public function index()
    {
        $courts = $this->courtService->listCourts(['status' => 'active']);
        $testimonials = $this->testimonialRepo->getActive();

        return Inertia::render('Public/Landing', [
            'courts' => $courts,
            'testimonials' => $testimonials
        ]);
    }

    /**
     * API endpoint to get available slots (and booking status) for a court on a specific date.
     */
    public function getCourtSlots(Request $request, int $id)
    {
        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today']
        ]);

        $court = Court::findOrFail($id);
        $date = $request->query('date');

        // Fetch slots with booking availability status
        $slots = $this->bookingService->getAvailableSlots($id, $date);

        return response()->json([
            'success' => true,
            'data' => [
                'court' => $court->only(['id', 'name', 'type', 'price', 'slot_duration']),
                'date' => $date,
                'slots' => $slots
            ]
        ]);
    }
}
