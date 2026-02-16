<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\GpayScreenshotParser;

class ProcessGpayScreenshot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public Upload $upload,
    ) {}

    public function handle(GpayScreenshotParser $parser): void
    {
        Log::info("ProcessGpayScreenshot: starting upload #{$this->upload->id}");
        $this->upload->update(['status' => 'processing']);

        try {
            Log::info("ProcessGpayScreenshot: sending to AI parser for upload #{$this->upload->id}");
            $transactions = $parser->parse(
                Storage::path($this->upload->file_path)
            );

            if (isset($transactions['sender_name'])) {
                $transactions = [$transactions];
            }

            Log::info("ProcessGpayScreenshot: AI returned " . count($transactions) . " transactions for upload #{$this->upload->id}");

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

            Log::info("ProcessGpayScreenshot: completed upload #{$this->upload->id} with " . count($transactions) . " transactions");
        } catch (\Throwable $e) {
            Log::error("ProcessGpayScreenshot: failed upload #{$this->upload->id} — {$e->getMessage()}");
            $this->upload->update(['status' => 'failed']);

            throw $e;
        }
    }
}
