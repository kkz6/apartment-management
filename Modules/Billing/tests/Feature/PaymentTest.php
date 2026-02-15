<?php

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;

it('can create a payment linked to a charge and unit', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create(['unit_id' => $unit->id, 'amount' => 2000.00]);

    $payment = Payment::factory()->create([
        'charge_id' => $charge->id,
        'unit_id' => $unit->id,
        'amount' => 2000.00,
        'source' => 'gpay',
    ]);

    expect($payment->charge->id)->toBe($charge->id)
        ->and($payment->unit->id)->toBe($unit->id)
        ->and((float) $payment->amount)->toBe(2000.00);
});

it('updates charge status to paid when fully paid', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create(['unit_id' => $unit->id, 'amount' => 2000.00]);

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'unit_id' => $unit->id,
        'amount' => 2000.00,
    ]);

    $charge->updateStatus();

    expect($charge->fresh()->status)->toBe('paid');
});

it('updates charge status to partial when partially paid', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create(['unit_id' => $unit->id, 'amount' => 2000.00]);

    Payment::factory()->create([
        'charge_id' => $charge->id,
        'unit_id' => $unit->id,
        'amount' => 1000.00,
    ]);

    $charge->updateStatus();

    expect($charge->fresh()->status)->toBe('partial');
});
