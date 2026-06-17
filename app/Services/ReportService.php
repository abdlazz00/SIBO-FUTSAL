<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\Court;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Parse date range from period string ('today', 'this_month', 'this_year', 'last_30_days', 'last_7_days')
     */
    public function parsePeriod(string $period): array
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        switch ($period) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last_30_days':
                $startDate = Carbon::now()->subDays(29)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ];
    }

    /**
     * Get financial summary (revenue, expense, net profit)
     */
    public function getFinancialSummary(string $startDate, string $endDate): array
    {
        // Net Revenue = SUM(amount - refund_amount) for confirmed payments within date range
        $revenue = (float) Payment::whereNotNull('confirmed_at')
            ->whereDate('confirmed_at', '>=', $startDate)
            ->whereDate('confirmed_at', '<=', $endDate)
            ->selectRaw('SUM(amount - refund_amount) as total')
            ->value('total') ?? 0;

        // Total Expense = SUM(amount)
        $expense = (float) Expense::whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->sum('amount');

        return [
            'total_revenue' => $revenue,
            'total_expense' => $expense,
            'net_profit' => $revenue - $expense
        ];
    }

    /**
     * Get revenue breakdown by payment method
     */
    public function getRevenueByMethod(string $startDate, string $endDate): array
    {
        $data = Payment::whereNotNull('confirmed_at')
            ->whereDate('confirmed_at', '>=', $startDate)
            ->whereDate('confirmed_at', '<=', $endDate)
            ->select('payment_method', DB::raw('SUM(amount - refund_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $result = [
            'cash' => 0,
            'transfer' => 0,
            'qris' => 0
        ];

        foreach ($data as $item) {
            $method = strtolower($item->payment_method ?? 'cash');
            if (array_key_exists($method, $result)) {
                $result[$method] = (float) $item->total;
            }
        }

        return $result;
    }

    /**
     * Get revenue and booking count by Court
     */
    public function getRevenueByCourt(string $startDate, string $endDate): array
    {
        // Join courts with bookings and payments to get count and total revenue
        $courts = Court::all();

        $data = DB::table('bookings')
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->whereNotNull('payments.confirmed_at')
            ->whereDate('payments.confirmed_at', '>=', $startDate)
            ->whereDate('payments.confirmed_at', '<=', $endDate)
            ->select('bookings.court_id', DB::raw('COUNT(bookings.id) as bookings_count'), DB::raw('SUM(payments.amount - payments.refund_amount) as total_revenue'))
            ->groupBy('bookings.court_id')
            ->get()
            ->keyBy('court_id');

        $result = [];
        foreach ($courts as $court) {
            $courtData = $data->get($court->id);
            $result[] = [
                'court_id' => $court->id,
                'court_name' => $court->name,
                'bookings_count' => $courtData ? (int) $courtData->bookings_count : 0,
                'revenue' => $courtData ? (float) $courtData->total_revenue : 0.0
            ];
        }

        return $result;
    }

    /**
     * Get expense breakdown by category
     */
    public function getExpenseByCategory(string $startDate, string $endDate): array
    {
        $data = Expense::whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        $result = [
            'utilities' => 0,
            'maintenance' => 0,
            'salaries' => 0,
            'other' => 0
        ];

        foreach ($data as $item) {
            $cat = strtolower($item->category);
            if (array_key_exists($cat, $result)) {
                $result[$cat] = (float) $item->total;
            } else {
                $result['other'] += (float) $item->total;
            }
        }

        return $result;
    }

    /**
     * Get monthly financial trend for a specific year (Revenue, Expense, Profit)
     */
    public function getMonthlyTrend(int $year): array
    {
        $months = range(1, 12);
        
        // Fetch revenue grouped by month
        $revenueData = Payment::whereNotNull('confirmed_at')
            ->whereYear('confirmed_at', $year)
            ->select(DB::raw('EXTRACT(MONTH FROM confirmed_at) as month'), DB::raw('SUM(amount - refund_amount) as total'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM confirmed_at)'))
            ->pluck('total', 'month')
            ->toArray();

        // Fetch expenses grouped by month
        $expenseData = Expense::whereYear('expense_date', $year)
            ->select(DB::raw('EXTRACT(MONTH FROM expense_date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('EXTRACT(MONTH FROM expense_date)'))
            ->pluck('total', 'month')
            ->toArray();

        $trend = [];
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        foreach ($months as $m) {
            // Postgres or Mysql can return decimals/strings or floats depending on the driver
            // Key might be float or integer in php array depending on extraction type
            $rev = 0;
            $exp = 0;

            foreach ($revenueData as $key => $val) {
                if ((int)$key === $m) {
                    $rev = (float)$val;
                    break;
                }
            }

            foreach ($expenseData as $key => $val) {
                if ((int)$key === $m) {
                    $exp = (float)$val;
                    break;
                }
            }

            $trend[] = [
                'month' => $m,
                'name' => $monthNames[$m],
                'revenue' => $rev,
                'expense' => $exp,
                'profit' => $rev - $exp
            ];
        }

        return $trend;
    }
}
