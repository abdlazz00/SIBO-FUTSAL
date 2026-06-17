<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $stats = $this->dashboardService->getAdminStats();
        $recentBookings = $this->dashboardService->getRecentBookings(10);
        $topCourts = $this->dashboardService->getTopCourts(5);
        $occupancyRates = $this->dashboardService->getOccupancyRates();
        $peakHours = $this->dashboardService->getPeakHoursFrequency();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'topCourts' => $topCourts,
            'occupancyRates' => $occupancyRates,
            'peakHours' => $peakHours,
        ]);
    }
}
