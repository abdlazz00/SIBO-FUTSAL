<?php

namespace App\Repositories\Contracts;

use App\Models\Expense;

interface ExpenseRepositoryInterface
{
    public function getAll(array $filters = []);
    public function findById(int $id): Expense;
    public function create(array $data): Expense;
    public function update(int $id, array $data): Expense;
    public function delete(int $id): bool;
}
