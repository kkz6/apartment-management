<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Models\Expense;

class ExpensesController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Billing/Expenses/Index', [
            'expenses' => Expense::orderByDesc('paid_date')->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Billing/Expenses/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
            'category' => ['required', 'in:electricity,water,maintenance,service,other'],
            'source' => ['required', 'in:gpay,bank_transfer,cash'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        Expense::create(array_merge($validated, [
            'reconciliation_status' => 'manual',
        ]));

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense): Response
    {
        return Inertia::render('Billing/Expenses/Edit', [
            'expense' => $expense,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
            'category' => ['required', 'in:electricity,water,maintenance,service,other'],
            'source' => ['required', 'in:gpay,bank_transfer,cash'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
