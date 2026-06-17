<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Exception;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'start_date', 'end_date']);
        $expenses = $this->expenseService->listExpenses($filters);
        $summary = $this->expenseService->getExpenseSummary($filters);

        return Inertia::render('Admin/Expenses/Index', [
            'expenses' => $expenses,
            'summary' => $summary,
            'filters' => $filters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:utilities,maintenance,salaries,other',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        $validated['recorded_by'] = auth()->id();

        try {
            $this->expenseService->createExpense($validated);

            return redirect()->back()->with('flash', [
                'success' => 'Pengeluaran berhasil dicatat!'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:utilities,maintenance,salaries,other',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
        ]);

        try {
            $this->expenseService->updateExpense($id, $validated);

            return redirect()->back()->with('flash', [
                'success' => 'Pengeluaran berhasil diperbarui!'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->expenseService->deleteExpense($id);

            return redirect()->back()->with('flash', [
                'success' => 'Pengeluaran berhasil dihapus!'
            ]);
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }
}
