<?php

namespace Modules\Telegram\Handlers;

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Payment;
use SergiX44\Nutgram\Nutgram;

class PaymentsCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = explode(' ', $text, 2);
        $flatNumber = trim($parts[1] ?? '');

        $query = Payment::with('unit:id,flat_number')
            ->orderByDesc('paid_date')
            ->limit(15);

        if ($flatNumber) {
            $unit = Unit::where('flat_number', $flatNumber)->first();

            if (! $unit) {
                $bot->sendMessage(text: "Unit {$flatNumber} not found.");

                return;
            }

            $query->where('unit_id', $unit->id);
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $bot->sendMessage(text: "No payments found.");

            return;
        }

        $title = $flatNumber ? "💰 Payments for unit {$flatNumber}" : "💰 Recent Payments";
        $lines = [$title, ""];

        foreach ($payments as $payment) {
            $flat = $payment->unit?->flat_number ?? '?';
            $date = $payment->paid_date->format('d-m-Y');
            $lines[] = "{$flat} | ₹" . number_format($payment->amount, 2) . " | {$date} | {$payment->source}";
        }

        $bot->sendMessage(text: implode("\n", $lines));
    }
}
