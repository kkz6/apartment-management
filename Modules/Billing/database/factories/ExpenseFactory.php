<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Billing\Models\Expense;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 500, 10000),
            'paid_date' => $this->faker->date(),
            'category' => $this->faker->randomElement(['electricity', 'water', 'maintenance', 'service', 'other']),
            'source' => $this->faker->randomElement(['gpay', 'bank_transfer', 'cash']),
            'reference_number' => $this->faker->optional()->uuid(),
            'reconciliation_status' => 'pending_verification',
        ];
    }
}
