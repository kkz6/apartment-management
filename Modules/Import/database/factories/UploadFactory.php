<?php

namespace Modules\Import\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Import\Models\Upload;

class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        return [
            'file_path' => 'uploads/' . $this->faker->uuid() . '.pdf',
            'type' => $this->faker->randomElement(['gpay_screenshot', 'bank_statement']),
            'status' => 'pending',
        ];
    }
}
