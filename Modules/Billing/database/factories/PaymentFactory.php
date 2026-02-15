<?php

namespace Modules\Billing\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'charge_id' => Charge::factory(),
            'unit_id' => Unit::factory(),
            'amount' => $this->faker->randomFloat(2, 500, 5000),
            'paid_date' => $this->faker->date(),
            'source' => $this->faker->randomElement(['gpay', 'bank_transfer', 'cash']),
            'reference_number' => $this->faker->optional()->uuid(),
            'matched_by' => 'manual',
            'reconciliation_status' => 'pending_verification',
        ];
    }
}
