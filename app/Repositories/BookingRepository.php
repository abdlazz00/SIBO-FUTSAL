<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;

class BookingRepository implements BookingRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Booking::query()->with(['court', 'user', 'created_by', 'cancelled_by', 'payment']);

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date']) && $filters['date'] !== '') {
            $query->whereDate('date', $filters['date']);
        }

        if (isset($filters['paginate']) && $filters['paginate'] === false) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
    }

    public function findById(int $id): Booking
    {
        return Booking::with(['court', 'user', 'cancelled_by', 'created_by', 'payment'])->findOrFail($id);
    }

    public function findByNumber(string $bookingNumber): ?Booking
    {
        return Booking::with(['court', 'user', 'cancelled_by', 'created_by', 'payment'])
            ->where('booking_number', $bookingNumber)
            ->first();
    }

    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    public function update(int $id, array $data): Booking
    {
        $booking = $this->findById($id);
        $booking->update($data);
        return $booking;
    }

    public function getByUserId(int $userId, array $filters = [])
    {
        $query = Booking::where('user_id', $userId)->with(['court', 'cancelled_by', 'payment']);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function checkConflict(int $courtId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        $query = Booking::where('court_id', $courtId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
