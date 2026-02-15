<?php

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Jobs\ProcessBankStatement;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\HdfcStatementParser;
use Modules\Import\Services\TransactionMatcher;

it('processes bank statement and creates parsed transactions', function () {
    $upload = Upload::factory()->create([
        'type' => 'bank_statement',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(HdfcStatementParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'date' => '2026-02-15',
            'narration' => 'UPI/Karthick S/Payment',
            'reference_number' => 'REF123',
            'amount' => 2000.00,
            'direction' => 'credit',
        ],
        [
            'date' => '2026-02-10',
            'narration' => 'BESCOM Electricity',
            'reference_number' => null,
            'amount' => 5000.00,
            'direction' => 'debit',
        ],
    ]);

    $matcher = app(TransactionMatcher::class);
    $job = new ProcessBankStatement($upload);
    $job->handle($mockParser, $matcher);

    expect($upload->fresh()->status)->toBe('processed')
        ->and($upload->fresh()->processed_at)->not->toBeNull()
        ->and(ParsedTransaction::count())->toBe(2)
        ->and(Expense::count())->toBe(1);
});

it('reconciles bank credit with existing payment', function () {
    $unit = Unit::factory()->create();
    $payment = Payment::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 2000.00,
        'paid_date' => '2026-02-15',
        'reconciliation_status' => 'pending_verification',
    ]);

    $upload = Upload::factory()->create(['type' => 'bank_statement']);

    $mockParser = Mockery::mock(HdfcStatementParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'date' => '2026-02-15',
            'narration' => 'UPI Credit',
            'amount' => 2000.00,
            'direction' => 'credit',
        ],
    ]);

    $matcher = app(TransactionMatcher::class);
    $job = new ProcessBankStatement($upload);
    $job->handle($mockParser, $matcher);

    expect($payment->fresh()->reconciliation_status)->toBe('bank_verified');
});

it('handles failed parsing gracefully', function () {
    $upload = Upload::factory()->create(['type' => 'bank_statement']);

    $mockParser = Mockery::mock(HdfcStatementParser::class);
    $mockParser->shouldReceive('parse')->once()->andThrow(new RuntimeException('PDF parse error'));

    $matcher = app(TransactionMatcher::class);
    $job = new ProcessBankStatement($upload);

    expect(fn () => $job->handle($mockParser, $matcher))->toThrow(RuntimeException::class);
    expect($upload->fresh()->status)->toBe('failed');
});

it('creates expenses for debit transactions from bank statement', function () {
    $upload = Upload::factory()->create([
        'type' => 'bank_statement',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(HdfcStatementParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'date' => '2026-02-10',
            'narration' => 'BESCOM Electricity',
            'reference_number' => null,
            'amount' => 5000.00,
            'direction' => 'debit',
        ],
    ]);

    $matcher = app(TransactionMatcher::class);
    $job = new ProcessBankStatement($upload);
    $job->handle($mockParser, $matcher);

    expect(Expense::count())->toBe(1)
        ->and(Expense::first()->description)->toBe('BESCOM Electricity')
        ->and((float) Expense::first()->amount)->toBe(5000.00);
});

it('generates fingerprints for bank statement transactions', function () {
    $upload = Upload::factory()->create([
        'type' => 'bank_statement',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(HdfcStatementParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'date' => '2026-02-15',
            'narration' => 'UPI/Karthick S/Payment',
            'amount' => 2000.00,
            'direction' => 'credit',
        ],
    ]);

    $matcher = app(TransactionMatcher::class);
    $job = new ProcessBankStatement($upload);
    $job->handle($mockParser, $matcher);

    $transaction = ParsedTransaction::first();
    $expectedFingerprint = ParsedTransaction::generateFingerprint(2000.00, '2026-02-15', 'UPI/Karthick S/Payment');

    expect($transaction->fingerprint)->toBe($expectedFingerprint);
});
