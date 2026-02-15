<?php

namespace Modules\Telegram\Handlers;

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;
use SergiX44\Nutgram\Nutgram;

class BalanceCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = explode(' ', $text, 2);
        $flatNumber = trim($parts[1] ?? '');

        if (! $flatNumber) {
            $bot->sendMessage(text: "Please specify a flat number: /balance 101");

            return;
        }

        $unit = Unit::with('residents:id,unit_id,name')
            ->where('flat_number', $flatNumber)
            ->first();

        if (! $unit) {
            $bot->sendMessage(text: "Unit {$flatNumber} not found.");

            return;
        }

        $totalDue = (float) Charge::where('unit_id', $unit->id)->sum('amount');
        $totalPaid = (float) Payment::where('unit_id', $unit->id)->sum('amount');
        $balance = $totalDue - $totalPaid;
        $resident = $unit->residents->first()?->name ?? '-';

        $bot->sendMessage(text: implode("\n", [
            "🏠 Unit {$unit->flat_number} ({$unit->flat_type})",
            "Resident: {$resident}",
            "",
            "Total due: ₹" . number_format($totalDue, 2),
            "Total paid: ₹" . number_format($totalPaid, 2),
            "Balance: ₹" . number_format($balance, 2),
        ]));
    }
}
