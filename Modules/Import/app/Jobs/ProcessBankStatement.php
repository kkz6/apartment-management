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
use Modules\Import\Services\HdfcStatementParser;
use Modules\Import\Services\TransactionMatcher;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProcessBankStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public Upload $upload,
        public ?string $password = null,
    ) {}

    public function handle(HdfcStatementParser $parser, TransactionMatcher $matcher): void
    {
        Log::info("ProcessBankStatement: starting upload #{$this->upload->id}");
        $this->upload->update(['status' => 'processing']);
        $decryptedPath = null;

        try {
            $pdfPath = Storage::path($this->upload->file_path);

            if ($this->password) {
                Log::info("ProcessBankStatement: decrypting PDF for upload #{$this->upload->id}");
                $decryptedPath = $this->decryptPdf($pdfPath, $this->password);
                $pdfPath = $decryptedPath;
            }

            Log::info("ProcessBankStatement: sending to AI parser for upload #{$this->upload->id}");
            $transactions = $parser->parse($pdfPath);
            Log::info("ProcessBankStatement: AI returned " . count($transactions) . " transactions for upload #{$this->upload->id}");

            foreach ($transactions as $txn) {
                $senderName = $txn['narration'] ?? $txn['sender_name'] ?? null;

                $parsed = $this->upload->parsedTransactions()->create([
                    'raw_text' => json_encode($txn),
                    'sender_name' => $senderName,
                    'amount' => $txn['amount'],
                    'date' => $txn['date'],
                    'direction' => $txn['direction'],
                    'fingerprint' => ParsedTransaction::generateFingerprint(
                        (float) $txn['amount'],
                        $txn['date'],
                        $senderName,
                    ),
                ]);

                $matcher->reconcileFromBank($parsed);
            }

            $this->upload->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            Log::info("ProcessBankStatement: completed upload #{$this->upload->id} with " . count($transactions) . " transactions");
        } catch (\Throwable $e) {
            Log::error("ProcessBankStatement: failed upload #{$this->upload->id} — {$e->getMessage()}");
            $this->upload->update(['status' => 'failed']);

            throw $e;
        } finally {
            if ($decryptedPath && file_exists($decryptedPath)) {
                unlink($decryptedPath);
            }
        }
    }

    protected function decryptPdf(string $pdfPath, string $password): string
    {
        $decryptedPath = "{$pdfPath}.decrypted.pdf";

        $process = new Process([
            'qpdf',
            "--password={$password}",
            '--decrypt',
            $pdfPath,
            $decryptedPath,
        ]);

        $process->run();

        $exitCode = $process->getExitCode();

        if ($exitCode !== 0 && $exitCode !== 3) {
            throw new RuntimeException("Failed to decrypt PDF: {$process->getErrorOutput()}");
        }

        return $decryptedPath;
    }
}
