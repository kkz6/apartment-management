<?php

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;

it('can create a charge for a unit', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create([
        'unit_id' => $unit->id,
        'type' => 'maintenance',
        'amount' => 2000.00,
        'billing_month' => '2026-02',
        'status' => 'pending',
    ]);

    expect($charge->unit->id)->toBe($unit->id)
        ->and($charge->type)->toBe('maintenance')
        ->and((float) $charge->amount)->toBe(2000.00);
});

it('calculates balance amount correctly', function () {
    $charge = Charge::factory()->create([
        'amount' => 2000.00,
        'status' => 'pending',
    ]);

    expect($charge->balanceAmount())->toBe(2000.00)
        ->and($charge->paidAmount())->toBe(0.0);
});

it('can create a community-wide charge with null unit', function () {
    $charge = Charge::factory()->create([
        'unit_id' => null,
        'type' => 'ad-hoc',
        'description' => 'Festival fund',
    ]);

    expect($charge->unit)->toBeNull()
        ->and($charge->type)->toBe('ad-hoc');
});
