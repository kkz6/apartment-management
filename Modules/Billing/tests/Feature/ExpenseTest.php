<?php

use Modules\Billing\Models\Expense;

it('can create an expense', function () {
    $expense = Expense::factory()->create([
        'description' => 'Electricity bill',
        'amount' => 5000.00,
        'category' => 'electricity',
        'source' => 'gpay',
    ]);

    expect($expense->description)->toBe('Electricity bill')
        ->and((float) $expense->amount)->toBe(5000.00)
        ->and($expense->category)->toBe('electricity');
});

it('defaults reconciliation status to pending_verification', function () {
    $expense = Expense::factory()->create();

    expect($expense->reconciliation_status)->toBe('pending_verification');
});

it('defaults source to bank_transfer', function () {
    $expense = Expense::factory()->create(['source' => 'bank_transfer']);

    expect($expense->source)->toBe('bank_transfer');
});
