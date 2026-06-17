<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\ExportService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;
    protected $exportService;
    protected $paymentService;

    public function __construct(
        ReportService $reportService,
        ExportService $exportService,
        PaymentService $paymentService
    ) {
        $this->reportService = $reportService;
        $this->exportService = $exportService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $period = $request->input('period', 'this_month');
        
        if ($period === 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        } else {
            $parsed = $this->reportService->parsePeriod($period);
            $startDate = $parsed['start_date'];
            $endDate = $parsed['end_date'];
        }

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);
        $revenueByMethod = $this->reportService->getRevenueByMethod($startDate, $endDate);
        $revenueByCourt = $this->reportService->getRevenueByCourt($startDate, $endDate);
        $expenseByCategory = $this->reportService->getExpenseByCategory($startDate, $endDate);
        
        $year = (int) Carbon::parse($startDate)->year;
        $monthlyTrend = $this->reportService->getMonthlyTrend($year);

        // Get confirmed payments in the range for detail table
        $payments = $this->paymentService->listPayments([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'confirmed'
        ]);

        return Inertia::render('Owner/Reports/Index', [
            'summary' => $summary,
            'revenueByMethod' => $revenueByMethod,
            'revenueByCourt' => $revenueByCourt,
            'expenseByCategory' => $expenseByCategory,
            'monthlyTrend' => $monthlyTrend,
            'payments' => $payments,
            'filters' => [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ]);
    }

    public function export(Request $request)
    {
        $period = $request->input('period', 'this_month');
        
        if ($period === 'custom') {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        } else {
            $parsed = $this->reportService->parsePeriod($period);
            $startDate = $parsed['start_date'];
            $endDate = $parsed['end_date'];
        }

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);
        $revenueByCourt = $this->reportService->getRevenueByCourt($startDate, $endDate);
        $expenseByCategory = $this->reportService->getExpenseByCategory($startDate, $endDate);
        
        $year = (int) Carbon::parse($startDate)->year;
        $monthlyTrend = $this->reportService->getMonthlyTrend($year);

        return $this->exportService->exportFinancialReportCsv(
            $summary,
            $revenueByCourt,
            $expenseByCategory,
            $monthlyTrend
        );
    }
}
