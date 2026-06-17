<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Payment;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get key metrics for Admin Dashboard
     */
    public function getAdminStats(): array
    {
        $today = Carbon::today()->toDateString();

        // 1. Total bookings today
        $bookingsToday = Booking::whereDate('date', $today)
            ->where('status', '!=', 'cancelled')
            ->count();

        // 2. Revenue today
        $revenueToday = (float) Payment::whereNotNull('confirmed_at')
            ->whereDate('confirmed_at', $today)
            ->selectRaw('SUM(amount - refund_amount) as net')
            ->value('net') ?? 0;

        // 3. Active courts
        $activeCourts = Court::where('status', 'active')->count();

        // 4. Pending payment count (bookings status = confirmed)
        $pendingPayments = Booking::where('status', 'confirmed')->count();

        return [
            'bookings_today' => $bookingsToday,
            'revenue_today' => $revenueToday,
            'active_courts' => $activeCourts,
            'pending_payments' => $pendingPayments,
        ];
    }

    /**
     * Get recent bookings (limit 10)
     */
    public function getRecentBookings(int $limit = 10)
    {
        return Booking::with('court')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get busiest courts (top courts)
     */
    public function getTopCourts(int $limit = 5): array
    {
        $data = Booking::where('status', '!=', 'cancelled')
            ->select('court_id', DB::raw('COUNT(id) as bookings_count'))
            ->groupBy('court_id')
            ->orderByDesc('bookings_count')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($data as $item) {
            $court = Court::find($item->court_id);
            if ($court) {
                $result[] = [
                    'court_id' => $court->id,
                    'court_name' => $court->name,
                    'type' => $court->type,
                    'bookings_count' => (int) $item->bookings_count,
                    'price' => (float) $court->price,
                ];
            }
        }

        return $result;
    }

    /**
     * Calculate court occupancy rate for today
     */
    public function getOccupancyRates(): array
    {
        $today = Carbon::today()->toDateString();
        $courts = Court::where('status', 'active')->get();
        $result = [];

        foreach ($courts as $court) {
            // Calculate total possible slots in a day
            $open = Carbon::parse($court->open_time);
            $close = Carbon::parse($court->close_time);
            $duration = (int) $court->slot_duration;

            $diffMinutes = $open->diffInMinutes($close);
            $totalSlots = $duration > 0 ? (int) floor($diffMinutes / $duration) : 1;
            if ($totalSlots <= 0) $totalSlots = 1;

            // Count booked slots today
            $bookedSlots = Booking::where('court_id', $court->id)
                ->whereDate('date', $today)
                ->where('status', '!=', 'cancelled')
                ->count();

            $rate = Math_round(($bookedSlots / $totalSlots) * 100);
            if ($rate > 100) $rate = 100;

            $result[] = [
                'court_id' => $court->id,
                'court_name' => $court->name,
                'booked_slots' => $bookedSlots,
                'total_slots' => $totalSlots,
                'rate' => $rate,
            ];
        }

        return $result;
    }

    /**
     * Get booking frequency by starting hour (for peak hours heatmap)
     */
    public function getPeakHoursFrequency(): array
    {
        $data = Booking::where('status', '!=', 'cancelled')
            ->select(DB::raw('EXTRACT(HOUR FROM start_time) as hour'), DB::raw('COUNT(id) as count'))
            ->groupBy(DB::raw('EXTRACT(HOUR FROM start_time)'))
            ->orderBy('hour')
            ->get();

        $result = [];
        // Support standard hours from 08:00 to 23:00
        for ($h = 8; $h <= 23; $h++) {
            $count = 0;
            foreach ($data as $item) {
                if ((int)$item->hour === $h) {
                    $count = (int)$item->count;
                    break;
                }
            }
            $result[] = [
                'hour' => sprintf('%02d:00', $h),
                'count' => $count
            ];
        }

        return $result;
    }

    /**
     * Get key metrics for Owner Dashboard
     */
    public function getOwnerStats(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        // 1. Revenue this month
        $revenueMonth = (float) Payment::whereNotNull('confirmed_at')
            ->whereDate('confirmed_at', '>=', $startOfMonth)
            ->whereDate('confirmed_at', '<=', $endOfMonth)
            ->selectRaw('SUM(amount - refund_amount) as net')
            ->value('net') ?? 0;

        // 2. Expense this month
        $expenseMonth = (float) Expense::whereDate('expense_date', '>=', $startOfMonth)
            ->whereDate('expense_date', '<=', $endOfMonth)
            ->sum('amount');

        return [
            'revenue_month' => $revenueMonth,
            'expense_month' => $expenseMonth,
            'profit_month' => $revenueMonth - $expenseMonth,
        ];
    }
}

// Simple helper to avoid PHP 8 math discrepancies
function Math_round($val) {
    return (int) round($val);
}
