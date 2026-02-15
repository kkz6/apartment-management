<?php

namespace Modules\Telegram\Handlers;

use Modules\Billing\Models\Charge;
use Modules\Billing\Support\BillingQuarter;
use SergiX44\Nutgram\Nutgram;

class ChargesCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = explode(' ', $text, 2);
        $quarter = trim($parts[1] ?? '') ?: BillingQuarter::current();

        if (! BillingQuarter::isValid($quarter)) {
            $bot->sendMessage(text: "Invalid quarter format. Use: /charges 2026-Q1");

            return;
        }

        $charges = Charge::with('unit:id,flat_number')
            ->where('billing_month', $quarter)
            ->orderBy('unit_id')
            ->get();

        if ($charges->isEmpty()) {
            $bot->sendMessage(text: "No charges found for " . BillingQuarter::label($quarter));

            return;
        }

        $label = BillingQuarter::label($quarter);
        $lines = ["📄 Charges for {$label}", ""];

        foreach ($charges as $charge) {
            $flat = $charge->unit?->flat_number ?? '?';
            $statusIcon = match ($charge->status) {
                'paid' => '✅',
                'partial' => '🔶',
                default => '⏳',
            };
            $lines[] = "{$statusIcon} {$flat}: ₹" . number_format($charge->amount, 2) . " ({$charge->status})";
        }

        $total = $charges->sum('amount');
        $lines[] = "";
        $lines[] = "Total: ₹" . number_format($total, 2);

        $bot->sendMessage(text: implode("\n", $lines));
    }
}
