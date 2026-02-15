<?php

namespace Modules\Import\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;

class ParsedTransactionFactory extends Factory
{
    protected $model = ParsedTransaction::class;

    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 100, 10000);
        $date = $this->faker->date();
        $name = $this->faker->name();

        return [
            'upload_id' => Upload::factory(),
            'raw_text' => $this->faker->sentence(),
            'sender_name' => $name,
            'amount' => $amount,
            'date' => $date,
            'direction' => $this->faker->randomElement(['credit', 'debit']),
            'fingerprint' => ParsedTransaction::generateFingerprint($amount, $date, $name),
            'match_type' => 'unmatched',
            'reconciliation_status' => 'unmatched',
        ];
    }
}
