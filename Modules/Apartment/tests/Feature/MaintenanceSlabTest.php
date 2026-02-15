<?php

use Modules\Apartment\Models\MaintenanceSlab;

it('returns the active slab rate for a flat type', function () {
    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 1500.00,
        'effective_from' => '2025-01-01',
    ]);

    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 2000.00,
        'effective_from' => '2026-01-01',
    ]);

    $rate = MaintenanceSlab::currentRate('2BHK', '2026-02-15');

    expect($rate)->toBe(2000.00);
});

it('returns null when no slab exists', function () {
    $rate = MaintenanceSlab::currentRate('4BHK');

    expect($rate)->toBeNull();
});

it('returns older slab rate for past dates', function () {
    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 1500.00,
        'effective_from' => '2025-01-01',
    ]);

    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 2000.00,
        'effective_from' => '2026-01-01',
    ]);

    $rate = MaintenanceSlab::currentRate('2BHK', '2025-06-15');

    expect($rate)->toBe(1500.00);
});
