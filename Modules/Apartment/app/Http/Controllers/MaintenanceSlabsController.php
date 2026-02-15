<?php

namespace Modules\Apartment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Apartment\Models\MaintenanceSlab;

class MaintenanceSlabsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Apartment/MaintenanceSlabs/Index', [
            'slabs' => MaintenanceSlab::orderBy('flat_type')
                ->orderByDesc('effective_from')
                ->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Apartment/MaintenanceSlabs/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'flat_type' => ['required', 'in:1BHK,2BHK,3BHK'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
        ]);

        MaintenanceSlab::create($validated);

        return redirect()->route('maintenance-slabs.index')->with('success', 'Maintenance slab created.');
    }

    public function edit(MaintenanceSlab $maintenanceSlab): Response
    {
        return Inertia::render('Apartment/MaintenanceSlabs/Edit', [
            'slab' => $maintenanceSlab,
        ]);
    }

    public function update(Request $request, MaintenanceSlab $maintenanceSlab): RedirectResponse
    {
        $validated = $request->validate([
            'flat_type' => ['required', 'in:1BHK,2BHK,3BHK'],
            'amount' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
        ]);

        $maintenanceSlab->update($validated);

        return redirect()->route('maintenance-slabs.index')->with('success', 'Maintenance slab updated.');
    }

    public function destroy(MaintenanceSlab $maintenanceSlab): RedirectResponse
    {
        $maintenanceSlab->delete();

        return redirect()->route('maintenance-slabs.index')->with('success', 'Maintenance slab deleted.');
    }
}
