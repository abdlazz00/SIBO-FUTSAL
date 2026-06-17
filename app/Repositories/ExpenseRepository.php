<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Repositories\Contracts\ExpenseRepositoryInterface;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function getAll(array $filters = [])
    {
        $query = Expense::query()->with('recordedBy');

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        if (isset($filters['category']) && $filters['category'] !== '') {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query->whereDate('expense_date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query->whereDate('expense_date', '<=', $filters['end_date']);
        }

        return $query->orderBy('expense_date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    public function findById(int $id): Expense
    {
        return Expense::with('recordedBy')->findOrFail($id);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(int $id, array $data): Expense
    {
        $expense = $this->findById($id);
        $expense->update($data);
        return $expense;
    }

    public function delete(int $id): bool
    {
        $expense = $this->findById($id);
        return $expense->delete();
    }
}
