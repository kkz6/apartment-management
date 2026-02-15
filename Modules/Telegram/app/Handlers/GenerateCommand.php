<?php

namespace Modules\Telegram\Handlers;

use Modules\Billing\Services\MaintenanceChargeGenerator;
use Modules\Billing\Support\BillingQuarter;
use SergiX44\Nutgram\Nutgram;

class GenerateCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = explode(' ', $text, 2);
        $quarter = trim($parts[1] ?? '') ?: BillingQuarter::current();

        if (! BillingQuarter::isValid($quarter)) {
            $bot->sendMessage(text: "Invalid quarter format. Use: /generate 2026-Q1");

            return;
        }

        $generator = app(MaintenanceChargeGenerator::class);
        $charges = $generator->generate($quarter);

        $label = BillingQuarter::label($quarter);

        if ($charges->isEmpty()) {
            $bot->sendMessage(text: "No new charges generated for {$label}. All units may already have charges for this quarter.");

            return;
        }

        $total = $charges->sum('amount');

        $bot->sendMessage(text: implode("\n", [
            "✅ Generated {$charges->count()} charges for {$label}",
            "Total: ₹" . number_format($total, 2),
        ]));
    }
}
