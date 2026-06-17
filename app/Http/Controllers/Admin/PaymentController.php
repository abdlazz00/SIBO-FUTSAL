<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $exportService;

    public function __construct(PaymentService $paymentService, ExportService $exportService)
    {
        $this->paymentService = $paymentService;
        $this->exportService = $exportService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'payment_method', 'start_date', 'end_date']);
        $payments = $this->paymentService->listPayments($filters);
        $summary = $this->paymentService->getTransactionSummary($filters);
        
        $unpaidBookings = \App\Models\Booking::where('status', 'confirmed')
            ->with(['court', 'user'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'unpaidBookings' => $unpaidBookings,
            'summary' => $summary,
            'filters' => $filters
        ]);
    }

    public function confirm(Request $request, int $bookingId)
    {
        $request->validate([
            'payment_method' => 'required|string|in:cash,transfer,qris'
        ]);

        try {
            $this->paymentService->confirmPayment(
                $bookingId,
                $request->input('payment_method'),
                auth()->id()
            );

            return redirect()->back()->with('flash', [
                'success' => 'Pembayaran berhasil dikonfirmasi!'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function refund(Request $request, int $paymentId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|min:10'
        ]);

        try {
            $this->paymentService->processRefund(
                $paymentId,
                (float) $request->input('amount'),
                $request->input('reason')
            );

            return redirect()->back()->with('flash', [
                'success' => 'Refund berhasil diproses!'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'status', 'payment_method', 'start_date', 'end_date']);
        $payments = $this->paymentService->listPayments($filters);

        return $this->exportService->exportPaymentsCsv($payments);
    }
}
