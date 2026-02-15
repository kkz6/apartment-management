<?php

namespace Modules\Import\Services;

use Laravel\Ai\Files\LocalDocument;
use Modules\Import\Ai\TransactionExtractorAgent;
use RuntimeException;

class HdfcStatementParser
{
    public function __construct(
        protected TransactionExtractorAgent $agent,
    ) {}

    /**
     * Parse an HDFC bank statement PDF and return extracted transaction data.
     *
     * @return array<int, array{date: string, narration: string, reference_number: ?string, amount: float, direction: string}>
     */
    public function parse(string $pdfPath): array
    {
        if (! file_exists($pdfPath)) {
            throw new RuntimeException("File not found: {$pdfPath}");
        }

        $response = $this->agent->prompt(
            'Parse this HDFC bank statement PDF. Extract all transactions as a JSON array. Each transaction should have: date (YYYY-MM-DD), narration (string), reference_number (string or null), amount (numeric, positive value), direction (credit if Deposit, debit if Withdrawal). The columns in HDFC statements are: Date, Narration, Chq/Ref Number, Value Date, Withdrawal Amt, Deposit Amt, Closing Balance. Return ONLY the JSON array, no markdown formatting.',
            [new LocalDocument($pdfPath)],
        );

        $text = trim($response->text);
        $text = $this->cleanJsonResponse($text);

        $transactions = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to parse AI response as JSON: " . json_last_error_msg());
        }

        if (isset($transactions['date'])) {
            return [$transactions];
        }

        return $transactions;
    }

    protected function cleanJsonResponse(string $text): string
    {
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        return trim($text);
    }
}
