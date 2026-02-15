<?php

namespace Modules\Import\Services;

use Laravel\Ai\Files\LocalImage;
use Modules\Import\Ai\TransactionExtractorAgent;
use RuntimeException;

class GpayScreenshotParser
{
    public function __construct(
        protected TransactionExtractorAgent $agent,
    ) {}

    /**
     * Parse a GPay screenshot and return extracted transaction data.
     *
     * @return array<int, array{sender_name: ?string, amount: float, date: string, direction: string}>
     */
    public function parse(string $imagePath): array
    {
        if (! file_exists($imagePath)) {
            throw new RuntimeException("Image file not found: {$imagePath}");
        }

        $response = $this->agent->prompt(
            'Extract all transaction details from this payment screenshot.',
            [new LocalImage($imagePath)],
        );

        $text = trim($response->text);
        $text = $this->cleanJsonResponse($text);

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to parse AI response as JSON: " . json_last_error_msg());
        }

        if (isset($decoded['sender_name'])) {
            return [$decoded];
        }

        return $decoded;
    }

    protected function cleanJsonResponse(string $text): string
    {
        $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);

        return trim($text);
    }
}
