<?php

namespace App\Http\Controllers\Owner;

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
        $ownerStats = $this->dashboardService->getOwnerStats();
        $adminStats = $this->dashboardService->getAdminStats();
        $recentBookings = $this->dashboardService->getRecentBookings(10);
        $topCourts = $this->dashboardService->getTopCourts(5);
        $occupancyRates = $this->dashboardService->getOccupancyRates();
        $peakHours = $this->dashboardService->getPeakHoursFrequency();

        return Inertia::render('Owner/Dashboard', [
            'ownerStats' => $ownerStats,
            'adminStats' => $adminStats,
            'recentBookings' => $recentBookings,
            'topCourts' => $topCourts,
            'occupancyRates' => $occupancyRates,
            'peakHours' => $peakHours,
        ]);
    }
}
