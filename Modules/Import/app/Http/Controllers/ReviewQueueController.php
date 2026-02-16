<?php

namespace Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Models\ParsedTransaction;

class ReviewQueueController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Import/ReviewQueue/Index', [
            'transactions' => ParsedTransaction::where('reconciliation_status', 'unmatched')
                ->with('upload')
                ->latest()
                ->paginate(15)
                ->through(fn ($t) => [
                    'id' => $t->id,
                    'sender_name' => $t->sender_name,
                    'amount' => $t->amount,
                    'date' => $t->date?->format('Y-m-d'),
                    'direction' => $t->direction,
                    'raw_text' => $t->raw_text,
                    'upload_type' => $t->upload?->type,
                ]),
            'units' => Unit::with('residents:id,unit_id,name')
                ->orderBy('flat_number')
                ->get(['id', 'flat_number']),
        ]);
    }

    public function assignPayment(Request $request, ParsedTransaction $parsedTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
        ]);

        $paidDate = $validated['date'] ?? $parsedTransaction->date;

        $charge = Charge::where('unit_id', $validated['unit_id'])
            ->where('status', '!=', 'paid')
            ->orderBy('billing_month')
            ->first();

        $payment = Payment::create([
            'charge_id' => $charge?->id,
            'unit_id' => $validated['unit_id'],
            'amount' => $parsedTransaction->amount,
            'paid_date' => $paidDate,
            'source' => $parsedTransaction->upload?->type === 'gpay_screenshot' ? 'gpay' : 'bank_transfer',
            'reference_number' => $validated['description'] ?? null,
            'matched_by' => 'manual',
            'reconciliation_status' => 'pending_verification',
            'added_by' => auth()->id(),
        ]);

        $charge?->updateStatus();

        $parsedTransaction->update([
            'match_type' => 'payment',
            'matched_payment_id' => $payment->id,
            'reconciliation_status' => 'manual_matched',
        ]);

        return redirect()->route('review-queue.index')->with('success', 'Transaction assigned to unit.');
    }

    public function assignExpense(Request $request, ParsedTransaction $parsedTransaction): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'in:electricity,water,maintenance,service,other'],
            'description' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
        ]);

        $paidDate = $validated['date'] ?? $parsedTransaction->date;

        $expense = Expense::create([
            'description' => $validated['description'] ?: ($parsedTransaction->sender_name ?? 'Unknown expense'),
            'amount' => $parsedTransaction->amount,
            'paid_date' => $paidDate,
            'category' => $validated['category'],
            'source' => $parsedTransaction->upload?->type === 'gpay_screenshot' ? 'gpay' : 'bank_transfer',
            'reconciliation_status' => 'pending_verification',
            'added_by' => auth()->id(),
        ]);

        $parsedTransaction->update([
            'match_type' => 'expense',
            'matched_expense_id' => $expense->id,
            'reconciliation_status' => 'manual_matched',
        ]);

        return redirect()->route('review-queue.index')->with('success', 'Transaction marked as expense.');
    }

    public function dismiss(ParsedTransaction $parsedTransaction): RedirectResponse
    {
        $parsedTransaction->update(['reconciliation_status' => 'reconciled']);

        return redirect()->route('review-queue.index')->with('success', 'Transaction dismissed.');
    }
}
