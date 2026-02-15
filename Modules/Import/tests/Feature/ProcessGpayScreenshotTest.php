<?php

use Modules\Import\Jobs\ProcessGpayScreenshot;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\GpayScreenshotParser;

it('processes a gpay screenshot and creates parsed transactions', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(GpayScreenshotParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'sender_name' => 'Karthick S',
            'amount' => 2000.00,
            'date' => '2026-02-15',
            'direction' => 'credit',
        ],
    ]);

    app()->instance(GpayScreenshotParser::class, $mockParser);

    $job = new ProcessGpayScreenshot($upload);
    $job->handle($mockParser);

    expect($upload->fresh()->status)->toBe('processed')
        ->and($upload->fresh()->processed_at)->not->toBeNull()
        ->and(ParsedTransaction::count())->toBe(1)
        ->and(ParsedTransaction::first()->sender_name)->toBe('Karthick S')
        ->and((float) ParsedTransaction::first()->amount)->toBe(2000.00);
});

it('processes multiple transactions from a single screenshot', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(GpayScreenshotParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'sender_name' => 'Karthick S',
            'amount' => 2000.00,
            'date' => '2026-02-15',
            'direction' => 'credit',
        ],
        [
            'sender_name' => 'Ravi K',
            'amount' => 1500.00,
            'date' => '2026-02-14',
            'direction' => 'debit',
        ],
    ]);

    $job = new ProcessGpayScreenshot($upload);
    $job->handle($mockParser);

    expect($upload->fresh()->status)->toBe('processed')
        ->and(ParsedTransaction::count())->toBe(2);
});

it('wraps a single transaction result in an array', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(GpayScreenshotParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        'sender_name' => 'Karthick S',
        'amount' => 2000.00,
        'date' => '2026-02-15',
        'direction' => 'credit',
    ]);

    $job = new ProcessGpayScreenshot($upload);
    $job->handle($mockParser);

    expect($upload->fresh()->status)->toBe('processed')
        ->and(ParsedTransaction::count())->toBe(1)
        ->and(ParsedTransaction::first()->sender_name)->toBe('Karthick S');
});

it('handles failed parsing gracefully', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(GpayScreenshotParser::class);
    $mockParser->shouldReceive('parse')->once()->andThrow(new RuntimeException('API error'));

    $job = new ProcessGpayScreenshot($upload);

    expect(fn () => $job->handle($mockParser))->toThrow(RuntimeException::class);
    expect($upload->fresh()->status)->toBe('failed');
});

it('generates fingerprints for parsed transactions', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    $mockParser = Mockery::mock(GpayScreenshotParser::class);
    $mockParser->shouldReceive('parse')->once()->andReturn([
        [
            'sender_name' => 'Karthick S',
            'amount' => 2000.00,
            'date' => '2026-02-15',
            'direction' => 'credit',
        ],
    ]);

    $job = new ProcessGpayScreenshot($upload);
    $job->handle($mockParser);

    $transaction = ParsedTransaction::first();
    $expectedFingerprint = ParsedTransaction::generateFingerprint(2000.00, '2026-02-15', 'Karthick S');

    expect($transaction->fingerprint)->toBe($expectedFingerprint);
});
