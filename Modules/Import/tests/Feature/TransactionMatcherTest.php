<?php

use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\TransactionMatcher;

beforeEach(function () {
    $this->matcher = new TransactionMatcher;
});

it('auto-matches credit to resident by gpay name', function () {
    $unit = Unit::factory()->create();
    Resident::factory()->create(['unit_id' => $unit->id, 'gpay_name' => 'Karthick S']);
    Charge::factory()->create(['unit_id' => $unit->id, 'status' => 'pending', 'amount' => 2000.00, 'billing_month' => '2026-01']);

    $parsed = ParsedTransaction::factory()->create([
        'sender_name' => 'Karthick S',
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'direction' => 'credit',
    ]);

    $this->matcher->match($parsed);

    $parsed->refresh();
    expect($parsed->reconciliation_status)->toBe('auto_matched')
        ->and($parsed->match_type)->toBe('payment')
        ->and($parsed->matched_payment_id)->not->toBeNull()
        ->and(Payment::count())->toBe(1);
});

it('creates expense for debit transactions', function () {
    $parsed = ParsedTransaction::factory()->create([
        'sender_name' => 'Electricity Board',
        'amount' => 5000.00,
        'date' => '2026-02-10',
        'direction' => 'debit',
    ]);

    $this->matcher->match($parsed);

    $parsed->refresh();
    expect($parsed->reconciliation_status)->toBe('auto_matched')
        ->and($parsed->match_type)->toBe('expense')
        ->and(Expense::count())->toBe(1)
        ->and(Expense::first()->description)->toBe('Electricity Board');
});

it('marks duplicate fingerprints as reconciled', function () {
    $upload = Upload::factory()->create();

    ParsedTransaction::factory()->create([
        'upload_id' => $upload->id,
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'sender_name' => 'Karthick S',
        'fingerprint' => ParsedTransaction::generateFingerprint(2000.00, '2026-02-15', 'Karthick S'),
        'reconciliation_status' => 'auto_matched',
    ]);

    $duplicate = ParsedTransaction::factory()->create([
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'sender_name' => 'Karthick S',
        'fingerprint' => ParsedTransaction::generateFingerprint(2000.00, '2026-02-15', 'Karthick S'),
        'direction' => 'credit',
    ]);

    $this->matcher->match($duplicate);

    expect($duplicate->fresh()->reconciliation_status)->toBe('reconciled');
});

it('reconciles bank credit with existing payment', function () {
    $unit = Unit::factory()->create();
    $payment = Payment::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 2000.00,
        'paid_date' => '2026-02-15',
        'reconciliation_status' => 'pending_verification',
    ]);

    $parsed = ParsedTransaction::factory()->create([
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'direction' => 'credit',
    ]);

    $this->matcher->reconcileFromBank($parsed);

    expect($payment->fresh()->reconciliation_status)->toBe('bank_verified')
        ->and($parsed->fresh()->reconciliation_status)->toBe('reconciled');
});

it('reconciles bank debit with existing expense', function () {
    $expense = Expense::factory()->create([
        'amount' => 5000.00,
        'paid_date' => '2026-02-10',
        'reconciliation_status' => 'pending_verification',
    ]);

    $parsed = ParsedTransaction::factory()->create([
        'amount' => 5000.00,
        'date' => '2026-02-10',
        'direction' => 'debit',
    ]);

    $this->matcher->reconcileFromBank($parsed);

    expect($expense->fresh()->reconciliation_status)->toBe('bank_verified')
        ->and($parsed->fresh()->reconciliation_status)->toBe('reconciled');
});

it('leaves credit unmatched when no resident found', function () {
    $parsed = ParsedTransaction::factory()->create([
        'sender_name' => 'Unknown Person',
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'direction' => 'credit',
    ]);

    $this->matcher->match($parsed);

    expect($parsed->fresh()->reconciliation_status)->toBe('unmatched');
});
