<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\GpayScreenshotParser;

class ProcessGpayScreenshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Upload $upload,
    ) {}

    public function handle(GpayScreenshotParser $parser): void
    {
        $this->upload->update(['status' => 'processing']);

        try {
            $transactions = $parser->parse(
                storage_path("app/{$this->upload->file_path}")
            );

            if (isset($transactions['sender_name'])) {
                $transactions = [$transactions];
            }

            foreach ($transactions as $txn) {
                $this->upload->parsedTransactions()->create([
                    'raw_text' => json_encode($txn),
                    'sender_name' => $txn['sender_name'] ?? null,
                    'amount' => $txn['amount'],
                    'date' => $txn['date'],
                    'direction' => $txn['direction'] ?? 'credit',
                    'fingerprint' => ParsedTransaction::generateFingerprint(
                        (float) $txn['amount'],
                        $txn['date'],
                        $txn['sender_name'] ?? null,
                    ),
                ]);
            }

            $this->upload->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $this->upload->update(['status' => 'failed']);

            throw $e;
        }
    }
}
