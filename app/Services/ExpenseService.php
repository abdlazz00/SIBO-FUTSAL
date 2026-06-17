<?php

namespace App\Services;

use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

class ExpenseService
{
    protected $expenseRepository;

    public function __construct(ExpenseRepositoryInterface $expenseRepository)
    {
        $this->expenseRepository = $expenseRepository;
    }

    public function listExpenses(array $filters = [])
    {
        return $this->expenseRepository->getAll($filters);
    }

    public function getExpenseById(int $id)
    {
        return $this->expenseRepository->findById($id);
    }

    public function createExpense(array $data): Expense
    {
        return $this->expenseRepository->create($data);
    }

    public function updateExpense(int $id, array $data): Expense
    {
        return $this->expenseRepository->update($id, $data);
    }

    public function deleteExpense(int $id): bool
    {
        return $this->expenseRepository->delete($id);
    }

    /**
     * Get aggregate expense summaries
     */
    public function getExpenseSummary(array $filters = []): array
    {
        $expenses = $this->expenseRepository->getAll($filters);

        $totalExpense = 0;
        $byCategory = [
            'utilities' => 0,
            'maintenance' => 0,
            'salaries' => 0,
            'other' => 0
        ];

        foreach ($expenses as $expense) {
            $amount = (float) $expense->amount;
            $totalExpense += $amount;

            $cat = strtolower($expense->category);
            if (array_key_exists($cat, $byCategory)) {
                $byCategory[$cat] += $amount;
            } else {
                $byCategory['other'] += $amount;
            }
        }

        return [
            'total_expense' => $totalExpense,
            'by_category' => $byCategory
        ];
    }
}
