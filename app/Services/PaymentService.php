<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class PaymentService
{
    protected $paymentRepository;
    protected $bookingRepository;
    protected $notificationService;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        BookingRepositoryInterface $bookingRepository,
        NotificationService $notificationService
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->bookingRepository = $bookingRepository;
        $this->notificationService = $notificationService;
    }

    public function listPayments(array $filters = [])
    {
        return $this->paymentRepository->getAll($filters);
    }

    public function getPaymentById(int $id)
    {
        return $this->paymentRepository->findById($id);
    }

    public function getPaymentByBookingId(int $bookingId)
    {
        return $this->paymentRepository->findByBookingId($bookingId);
    }

    /**
     * Confirm booking payment
     */
    public function confirmPayment(int $bookingId, string $method, int $adminId): Payment
    {
        return DB::transaction(function () use ($bookingId, $method, $adminId) {
            $booking = $this->bookingRepository->findById($bookingId);

            if ($booking->status === 'cancelled') {
                throw new Exception('Tidak bisa mengonfirmasi pembayaran untuk booking yang sudah dibatalkan.');
            }

            // Find existing or create new payment
            $payment = $this->paymentRepository->findByBookingId($bookingId);

            $paymentData = [
                'booking_id' => $bookingId,
                'payment_method' => $method,
                'amount' => $booking->total_price,
                'confirmed_by' => $adminId,
                'confirmed_at' => Carbon::now(),
            ];

            if ($payment) {
                $payment = $this->paymentRepository->update($payment->id, $paymentData);
            } else {
                $payment = $this->paymentRepository->create($paymentData);
            }

            // Update booking status to completed
            $updatedBooking = $this->bookingRepository->update($bookingId, [
                'status' => 'completed'
            ]);

            // Dispatch notification
            $updatedBooking->load('court');
            $this->notificationService->notifyPaymentConfirmed($updatedBooking, $payment);

            return $payment;
        });
    }

    /**
     * Process payment refund
     */
    public function processRefund(int $paymentId, float $amount, string $reason): Payment
    {
        return DB::transaction(function () use ($paymentId, $amount, $reason) {
            $payment = $this->paymentRepository->findById($paymentId);

            $availableRefund = (float) $payment->amount - (float) $payment->refund_amount;
            if ($amount > $availableRefund) {
                throw new Exception('Jumlah refund melebihi sisa dana pembayaran yang tersedia.');
            }

            $updatedPayment = $this->paymentRepository->update($paymentId, [
                'refund_amount' => (float) $payment->refund_amount + $amount,
                'refund_reason' => $reason
            ]);

            // If fully or partially refunded, we can mark the booking as cancelled (optional, let's cancel it if refund matches amount)
            if ($updatedPayment->refund_amount >= $updatedPayment->amount) {
                $this->bookingRepository->update($payment->booking_id, [
                    'status' => 'cancelled'
                ]);
            }

            return $updatedPayment;
        });
    }

    /**
     * Get transaction financial summary
     */
    public function getTransactionSummary(array $filters = []): array
    {
        $paymentsQuery = Payment::whereNotNull('confirmed_at');

        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $paymentsQuery->whereDate('confirmed_at', '>=', $filters['start_date']);
        }
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $paymentsQuery->whereDate('confirmed_at', '<=', $filters['end_date']);
        }

        $payments = $paymentsQuery->get();

        $totalRevenue = 0;
        $totalRefunded = 0;
        $byMethod = [
            'cash' => 0,
            'transfer' => 0,
            'qris' => 0
        ];

        foreach ($payments as $payment) {
            $netAmount = (float) $payment->amount - (float) $payment->refund_amount;
            $totalRevenue += $netAmount;
            $totalRefunded += (float) $payment->refund_amount;

            $method = strtolower($payment->payment_method);
            if (array_key_exists($method, $byMethod)) {
                $byMethod[$method] += $netAmount;
            }
        }

        // Today's revenue
        $todayRevenue = (float) Payment::whereNotNull('confirmed_at')
            ->whereDate('confirmed_at', Carbon::today())
            ->selectRaw('SUM(amount - refund_amount) as net')
            ->value('net') ?? 0;

        // This month's revenue
        $thisMonthRevenue = (float) Payment::whereNotNull('confirmed_at')
            ->whereMonth('confirmed_at', Carbon::now()->month)
            ->whereYear('confirmed_at', Carbon::now()->year)
            ->selectRaw('SUM(amount - refund_amount) as net')
            ->value('net') ?? 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_refunded' => $totalRefunded,
            'today_revenue' => $todayRevenue,
            'this_month_revenue' => $thisMonthRevenue,
            'by_method' => $byMethod,
        ];
    }
}
