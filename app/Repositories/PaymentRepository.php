<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Payment::query()->with(['booking.court', 'booking.user', 'confirmedBy']);

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->whereHas('booking', function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            if ($filters['status'] === 'confirmed') {
                $query->whereNotNull('confirmed_at')->where('refund_amount', 0);
            } elseif ($filters['status'] === 'pending') {
                $query->whereNull('confirmed_at');
            } elseif ($filters['status'] === 'refunded') {
                $query->where('refund_amount', '>', 0);
            }
        }

        if (isset($filters['payment_method']) && $filters['payment_method'] !== '') {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (isset($filters['paginate']) && $filters['paginate'] === false) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
    }

    public function findById(int $id): Payment
    {
        return Payment::with(['booking.court', 'booking.user', 'confirmedBy'])->findOrFail($id);
    }

    public function findByBookingId(int $bookingId): ?Payment
    {
        return Payment::with(['booking.court', 'booking.user', 'confirmedBy'])
            ->where('booking_id', $bookingId)
            ->first();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function update(int $id, array $data): Payment
    {
        $payment = $this->findById($id);
        $payment->update($data);
        return $payment;
    }
}
