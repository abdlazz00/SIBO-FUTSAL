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

        $confirmedBookingId = $request->input('confirmed_booking');
        $confirmedPayment = null;
        if ($confirmedBookingId) {
            $payment = \App\Models\Payment::where('booking_id', $confirmedBookingId)
                ->with(['booking.court', 'confirmed_by_user'])
                ->first();
            if ($payment) {
                $confirmedPayment = [
                    'booking_number' => $payment->booking->booking_number,
                    'customer_name' => $payment->booking->customer_name,
                    'court_name' => $payment->booking->court->name ?? '-',
                    'date' => $payment->booking->date,
                    'start_time' => $payment->booking->start_time,
                    'end_time' => $payment->booking->end_time,
                    'total_price' => (float)$payment->amount,
                    'payment_method' => $payment->payment_method,
                    'cash_received' => (float)$payment->cash_received,
                    'cash_change' => (float)$payment->cash_change,
                    'confirmed_at' => $payment->confirmed_at
                ];
            }
        }

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'unpaidBookings' => $unpaidBookings,
            'summary' => $summary,
            'filters' => $filters,
            'confirmedPayment' => $confirmedPayment
        ]);
    }

    public function confirm(Request $request, int $bookingId)
    {
        $booking = \App\Models\Booking::findOrFail($bookingId);

        $rules = [
            'payment_method' => 'required|string|in:cash,transfer,qris'
        ];

        if ($request->input('payment_method') === 'cash') {
            $rules['cash_received'] = 'required|numeric|min:' . $booking->total_price;
        }

        $validated = $request->validate($rules);

        $cashReceived = (float) ($validated['cash_received'] ?? 0);
        $cashChange = max(0.0, $cashReceived - (float) $booking->total_price);

        try {
            $payment = $this->paymentService->confirmPayment(
                $bookingId,
                $request->input('payment_method'),
                auth()->id(),
                $cashReceived,
                $cashChange
            );

            return redirect()->route('admin.payments.index', ['confirmed_booking' => $bookingId])
                ->with('success', 'Pembayaran berhasil dikonfirmasi!');
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
        $filters['paginate'] = false;
        $payments = $this->paymentService->listPayments($filters);

        return $this->exportService->exportPaymentsCsv($payments);
    }
}
