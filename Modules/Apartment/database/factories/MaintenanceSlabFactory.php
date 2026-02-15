<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\MaintenanceSlab;

class MaintenanceSlabFactory extends Factory
{
    protected $model = MaintenanceSlab::class;

    public function definition(): array
    {
        return [
            'flat_type' => $this->faker->randomElement(['1BHK', '2BHK', '3BHK']),
            'amount' => $this->faker->randomFloat(2, 1000, 5000),
            'effective_from' => $this->faker->date(),
        ];
    }
}
