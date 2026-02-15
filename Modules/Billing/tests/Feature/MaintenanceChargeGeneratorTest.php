<?php

use Modules\Apartment\Models\MaintenanceSlab;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Services\MaintenanceChargeGenerator;

it('generates charges for all units based on slab rates', function () {
    MaintenanceSlab::factory()->create(['flat_type' => '2BHK', 'amount' => 2000.00, 'effective_from' => '2025-01-01']);
    MaintenanceSlab::factory()->create(['flat_type' => '3BHK', 'amount' => 3000.00, 'effective_from' => '2025-01-01']);

    Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Unit::factory()->create(['flat_number' => '102', 'flat_type' => '3BHK']);

    $generator = new MaintenanceChargeGenerator;
    $charges = $generator->generate('2026-02');

    expect($charges)->toHaveCount(2)
        ->and(Charge::count())->toBe(2);
});

it('skips units that already have charges for the month', function () {
    MaintenanceSlab::factory()->create(['flat_type' => '2BHK', 'amount' => 2000.00, 'effective_from' => '2025-01-01']);

    $unit = Unit::factory()->create(['flat_type' => '2BHK']);
    Charge::factory()->create(['unit_id' => $unit->id, 'billing_month' => '2026-02', 'type' => 'maintenance']);

    $generator = new MaintenanceChargeGenerator;
    $charges = $generator->generate('2026-02');

    expect($charges)->toHaveCount(0)
        ->and(Charge::where('unit_id', $unit->id)->where('billing_month', '2026-02')->count())->toBe(1);
});

it('skips units with no slab defined', function () {
    Unit::factory()->create(['flat_type' => '4BHK']);

    $generator = new MaintenanceChargeGenerator;
    $charges = $generator->generate('2026-02');

    expect($charges)->toHaveCount(0);
});

it('sets due date when provided', function () {
    MaintenanceSlab::factory()->create(['flat_type' => '2BHK', 'amount' => 2000.00, 'effective_from' => '2025-01-01']);
    Unit::factory()->create(['flat_type' => '2BHK']);

    $generator = new MaintenanceChargeGenerator;
    $charges = $generator->generate('2026-02', '2026-02-28');

    expect($charges->first()->due_date->format('Y-m-d'))->toBe('2026-02-28');
});
