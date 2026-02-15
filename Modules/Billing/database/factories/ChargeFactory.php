<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;

class ChargeFactory extends Factory
{
    protected $model = Charge::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'type' => $this->faker->randomElement(['maintenance', 'ad-hoc']),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 1000, 5000),
            'billing_month' => $this->faker->date('Y-m'),
            'due_date' => $this->faker->date(),
            'status' => 'pending',
        ];
    }
}
