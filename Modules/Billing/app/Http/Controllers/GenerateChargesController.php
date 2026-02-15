<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Billing\Services\MaintenanceChargeGenerator;

class GenerateChargesController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Billing/Charges/Generate');
    }

    public function store(Request $request, MaintenanceChargeGenerator $generator): RedirectResponse
    {
        $validated = $request->validate([
            'billing_month' => ['required', 'string', 'max:7'],
            'due_date' => ['nullable', 'date'],
        ]);

        $charges = $generator->generate($validated['billing_month'], $validated['due_date'] ?? null);

        return redirect()->route('charges.index')->with('success', "Generated {$charges->count()} maintenance charges for {$validated['billing_month']}.");
    }
}
