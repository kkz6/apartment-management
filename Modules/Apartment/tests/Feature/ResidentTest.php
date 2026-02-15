<?php

use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;

it('can create a resident linked to a unit', function () {
    $unit = Unit::factory()->create(['flat_number' => '201']);
    $resident = Resident::factory()->create([
        'unit_id' => $unit->id,
        'name' => 'Karthick',
        'gpay_name' => 'Karthick S',
    ]);

    expect($resident->unit->flat_number)->toBe('201')
        ->and($resident->gpay_name)->toBe('Karthick S');
});

it('cascades delete when unit is deleted', function () {
    $unit = Unit::factory()->create();
    Resident::factory()->create(['unit_id' => $unit->id]);

    $unit->delete();

    expect(Resident::count())->toBe(0);
});

it('belongs to a unit', function () {
    $resident = Resident::factory()->create();

    expect($resident->unit)->toBeInstanceOf(Unit::class);
});
