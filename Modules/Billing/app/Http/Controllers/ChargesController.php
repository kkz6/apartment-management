<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;

class ChargesController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Charge::with('unit:id,flat_number')
            ->orderByDesc('billing_month')
            ->orderBy('unit_id');

        if ($request->filled('billing_month')) {
            $query->where('billing_month', $request->input('billing_month'));
        }

        return Inertia::render('Billing/Charges/Index', [
            'charges' => $query->paginate(15),
            'filters' => [
                'billing_month' => $request->input('billing_month', ''),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Billing/Charges/Create', [
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apply_to_all' => ['boolean'],
            'unit_id' => ['required_unless:apply_to_all,true', 'nullable', 'exists:units,id'],
            'type' => ['required', 'in:maintenance,ad-hoc'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_month' => ['required', 'string', 'regex:/^\d{4}-Q[1-4]$/'],
            'due_date' => ['nullable', 'date'],
        ]);

        unset($validated['apply_to_all']);

        if ($request->boolean('apply_to_all')) {
            $units = Unit::all();

            foreach ($units as $unit) {
                Charge::create(array_merge($validated, [
                    'unit_id' => $unit->id,
                    'status' => 'pending',
                ]));
            }

            return redirect()->route('charges.index')->with('success', "Charges created for {$units->count()} units.");
        }

        Charge::create(array_merge($validated, ['status' => 'pending']));

        return redirect()->route('charges.index')->with('success', 'Charge created.');
    }

    public function edit(Charge $charge): Response
    {
        return Inertia::render('Billing/Charges/Edit', [
            'charge' => $charge,
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
        ]);
    }

    public function update(Request $request, Charge $charge): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'type' => ['required', 'in:maintenance,ad-hoc'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'billing_month' => ['required', 'string', 'regex:/^\d{4}-Q[1-4]$/'],
            'due_date' => ['nullable', 'date'],
        ]);

        $charge->update($validated);

        return redirect()->route('charges.index')->with('success', 'Charge updated.');
    }

    public function destroy(Charge $charge): RedirectResponse
    {
        $charge->delete();

        return redirect()->route('charges.index')->with('success', 'Charge deleted.');
    }
}
