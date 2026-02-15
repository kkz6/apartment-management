<?php

use Modules\Apartment\Models\Unit;

it('can create a unit', function () {
    $unit = Unit::factory()->create([
        'flat_number' => '101',
        'flat_type' => '2BHK',
        'floor' => 1,
        'area_sqft' => 950.00,
    ]);

    expect($unit)->toBeInstanceOf(Unit::class)
        ->and($unit->flat_number)->toBe('101')
        ->and($unit->flat_type)->toBe('2BHK');
});

it('enforces unique flat numbers', function () {
    Unit::factory()->create(['flat_number' => '101']);
    Unit::factory()->create(['flat_number' => '101']);
})->throws(\Illuminate\Database\QueryException::class);
