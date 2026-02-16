<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;

class PaymentsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Billing/Payments/Index', [
            'payments' => Payment::with(['unit:id,flat_number', 'charge:id,description,billing_month,amount'])
                ->orderByDesc('paid_date')
                ->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Billing/Payments/Create', [
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
            'pendingCharges' => Charge::whereIn('status', ['pending', 'partial'])
                ->with('unit:id,flat_number')
                ->orderByDesc('billing_month')
                ->get(['id', 'unit_id', 'description', 'billing_month', 'amount', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'charge_id' => ['nullable', 'exists:charges,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
            'source' => ['required', 'in:gpay,bank_transfer,cash'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        $payment = Payment::create(array_merge($validated, [
            'reconciliation_status' => 'manual',
            'added_by' => auth()->id(),
        ]));

        if ($payment->charge_id) {
            $payment->charge->updateStatus();
        }

        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }

    public function edit(Payment $payment): Response
    {
        return Inertia::render('Billing/Payments/Edit', [
            'payment' => $payment->load(['unit:id,flat_number', 'charge:id,description,billing_month,amount']),
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
            'pendingCharges' => Charge::whereIn('status', ['pending', 'partial'])
                ->orWhere('id', $payment->charge_id)
                ->with('unit:id,flat_number')
                ->orderByDesc('billing_month')
                ->get(['id', 'unit_id', 'description', 'billing_month', 'amount', 'status']),
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'charge_id' => ['nullable', 'exists:charges,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_date' => ['required', 'date'],
            'source' => ['required', 'in:gpay,bank_transfer,cash'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        $oldChargeId = $payment->charge_id;
        $payment->update($validated);

        if ($oldChargeId && $oldChargeId !== $payment->charge_id) {
            Charge::find($oldChargeId)?->updateStatus();
        }

        if ($payment->charge_id) {
            $payment->charge->updateStatus();
        }

        return redirect()->route('payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $chargeId = $payment->charge_id;
        $payment->delete();

        if ($chargeId) {
            Charge::find($chargeId)?->updateStatus();
        }

        return redirect()->route('payments.index')->with('success', 'Payment deleted.');
    }
}
