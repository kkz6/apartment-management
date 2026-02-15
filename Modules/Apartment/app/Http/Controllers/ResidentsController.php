<?php

namespace Modules\Apartment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;

class ResidentsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Apartment/Residents/Index', [
            'residents' => Resident::with('unit:id,flat_number')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Apartment/Residents/Create', [
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_owner' => ['boolean'],
            'gpay_name' => ['nullable', 'string', 'max:255'],
        ]);

        Resident::create($validated);

        return redirect()->route('residents.index')->with('success', 'Resident created.');
    }

    public function edit(Resident $resident): Response
    {
        return Inertia::render('Apartment/Residents/Edit', [
            'resident' => $resident,
            'units' => Unit::orderBy('flat_number')->get(['id', 'flat_number']),
        ]);
    }

    public function update(Request $request, Resident $resident): RedirectResponse
    {
        $validated = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_owner' => ['boolean'],
            'gpay_name' => ['nullable', 'string', 'max:255'],
        ]);

        $resident->update($validated);

        return redirect()->route('residents.index')->with('success', 'Resident updated.');
    }

    public function destroy(Resident $resident): RedirectResponse
    {
        $resident->delete();

        return redirect()->route('residents.index')->with('success', 'Resident deleted.');
    }
}
