<?php

use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;

it('generates consistent fingerprints', function () {
    $fp1 = ParsedTransaction::generateFingerprint(1000.00, '2026-02-15', 'Karthick');
    $fp2 = ParsedTransaction::generateFingerprint(1000.00, '2026-02-15', 'Karthick');

    expect($fp1)->toBe($fp2);
});

it('generates different fingerprints for different data', function () {
    $fp1 = ParsedTransaction::generateFingerprint(1000.00, '2026-02-15', 'Karthick');
    $fp2 = ParsedTransaction::generateFingerprint(2000.00, '2026-02-15', 'Karthick');

    expect($fp1)->not->toBe($fp2);
});

it('normalizes name case in fingerprint', function () {
    $fp1 = ParsedTransaction::generateFingerprint(1000.00, '2026-02-15', 'Karthick');
    $fp2 = ParsedTransaction::generateFingerprint(1000.00, '2026-02-15', 'KARTHICK');

    expect($fp1)->toBe($fp2);
});

it('belongs to an upload', function () {
    $parsed = ParsedTransaction::factory()->create();

    expect($parsed->upload)->toBeInstanceOf(Upload::class);
});
