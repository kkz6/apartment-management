<?php

namespace Modules\Apartment\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;

class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'is_owner' => $this->faker->boolean(70),
            'gpay_name' => $this->faker->name(),
        ];
    }
}
