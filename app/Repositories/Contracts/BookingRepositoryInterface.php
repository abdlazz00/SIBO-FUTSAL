<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;

interface BookingRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id): Booking;
    public function findByNumber(string $bookingNumber): ?Booking;
    public function create(array $data): Booking;
    public function update(int $id, array $data): Booking;
    public function getByUserId(int $userId, array $filters = []);
    public function checkConflict(int $courtId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool;
}
