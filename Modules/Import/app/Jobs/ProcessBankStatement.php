<?php

namespace Modules\Import\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Import\Models\ParsedTransaction;
use Modules\Import\Models\Upload;
use Modules\Import\Services\HdfcStatementParser;
use Modules\Import\Services\TransactionMatcher;
use RuntimeException;
use Symfony\Component\Process\Process;

class ProcessBankStatement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Upload $upload,
        public ?string $password = null,
    ) {}

    public function handle(HdfcStatementParser $parser, TransactionMatcher $matcher): void
    {
        $this->upload->update(['status' => 'processing']);
        $decryptedPath = null;

        try {
            $pdfPath = storage_path("app/{$this->upload->file_path}");

            if ($this->password) {
                $decryptedPath = $this->decryptPdf($pdfPath, $this->password);
                $pdfPath = $decryptedPath;
            }

            $transactions = $parser->parse($pdfPath);

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
        } catch (\Throwable $e) {
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

        if (! $process->isSuccessful()) {
            throw new RuntimeException("Failed to decrypt PDF: {$process->getErrorOutput()}");
        }

        return $decryptedPath;
    }
}
