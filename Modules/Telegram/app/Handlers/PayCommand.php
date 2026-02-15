<?php

namespace Modules\Telegram\Handlers;

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;
use SergiX44\Nutgram\Nutgram;

class PayCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = preg_split('/\s+/', $text);

        // /pay {flat} {amount} {source?}
        $flatNumber = $parts[1] ?? null;
        $amount = $parts[2] ?? null;
        $source = $parts[3] ?? 'cash';

        if (! $flatNumber || ! $amount) {
            $bot->sendMessage(text: "Usage: /pay {flat} {amount} {source?}\nExample: /pay 101 6000 gpay\nSources: gpay, bank_transfer, cash (default)");

            return;
        }

        if (! is_numeric($amount) || (float) $amount <= 0) {
            $bot->sendMessage(text: "Invalid amount. Must be a positive number.");

            return;
        }

        if (! in_array($source, ['gpay', 'bank_transfer', 'cash'])) {
            $bot->sendMessage(text: "Invalid source. Use: gpay, bank_transfer, or cash");

            return;
        }

        $unit = Unit::where('flat_number', $flatNumber)->first();

        if (! $unit) {
            $bot->sendMessage(text: "Unit {$flatNumber} not found.");

            return;
        }

        $charge = Charge::where('unit_id', $unit->id)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('billing_month')
            ->first();

        $payment = Payment::create([
            'unit_id' => $unit->id,
            'charge_id' => $charge?->id,
            'amount' => (float) $amount,
            'paid_date' => now()->toDateString(),
            'source' => $source,
            'reconciliation_status' => 'manual',
        ]);

        $charge?->updateStatus();

        $chargeInfo = $charge
            ? " (linked to {$charge->description})"
            : " (no pending charge to link)";

        $bot->sendMessage(text: implode("\n", [
            "✅ Payment recorded",
            "",
            "Unit: {$unit->flat_number}",
            "Amount: ₹" . number_format($payment->amount, 2),
            "Source: {$source}",
            "Date: " . now()->format('d-m-Y'),
            $chargeInfo,
        ]));
    }
}
