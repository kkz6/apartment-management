<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Unit;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $flatTypes = ['1BHK', '2BHK', '3BHK'];
        $floor = $this->faker->numberBetween(1, 5);

        return [
            'flat_number' => "{$floor}{$this->faker->unique()->numberBetween(01, 10)}",
            'flat_type' => $this->faker->randomElement($flatTypes),
            'floor' => $floor,
            'area_sqft' => $this->faker->randomFloat(2, 500, 1500),
        ];
    }
}
