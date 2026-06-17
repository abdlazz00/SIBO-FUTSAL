<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id): Payment;
    public function findByBookingId(int $bookingId): ?Payment;
    public function create(array $data): Payment;
    public function update(int $id, array $data): Payment;
}
