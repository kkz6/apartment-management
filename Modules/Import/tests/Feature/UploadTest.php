<?php

use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;

it('can create an upload', function () {
    $upload = Upload::factory()->create([
        'type' => 'gpay_screenshot',
        'status' => 'pending',
    ]);

    expect($upload->type)->toBe('gpay_screenshot')
        ->and($upload->status)->toBe('pending');
});

it('has many parsed transactions', function () {
    $upload = Upload::factory()->create();
    ParsedTransaction::factory()->count(3)->create(['upload_id' => $upload->id]);

    expect($upload->parsedTransactions)->toHaveCount(3);
});
