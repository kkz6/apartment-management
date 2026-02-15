<?php

namespace Modules\Apartment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\Unit;

class UnitsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Apartment/Units/Index', [
            'units' => Unit::withCount('residents')->orderBy('flat_number')->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Apartment/Units/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'flat_number' => ['required', 'string', 'unique:units'],
            'flat_type' => ['required', 'in:1BHK,2BHK,3BHK'],
            'floor' => ['required', 'integer', 'min:0'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Unit created.');
    }

    public function edit(Unit $unit): Response
    {
        return Inertia::render('Apartment/Units/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'flat_number' => ['required', 'string', "unique:units,flat_number,{$unit->id}"],
            'flat_type' => ['required', 'in:1BHK,2BHK,3BHK'],
            'floor' => ['required', 'integer', 'min:0'],
            'area_sqft' => ['nullable', 'numeric', 'min:0'],
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted.');
    }
}
