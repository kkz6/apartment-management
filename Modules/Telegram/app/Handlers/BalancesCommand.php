<?php

namespace Modules\Telegram\Handlers;

use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;
use SergiX44\Nutgram\Nutgram;

class BalancesCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $units = Unit::orderBy('flat_number')->get();

        if ($units->isEmpty()) {
            $bot->sendMessage(text: "No units found.");

            return;
        }

        $lines = ["📋 All Unit Balances", ""];

        foreach ($units as $unit) {
            $totalDue = (float) Charge::where('unit_id', $unit->id)->sum('amount');
            $totalPaid = (float) Payment::where('unit_id', $unit->id)->sum('amount');
            $balance = $totalDue - $totalPaid;

            $status = $balance <= 0 ? '✅' : '⚠️';
            $lines[] = "{$status} {$unit->flat_number}: ₹" . number_format($balance, 2);
        }

        $bot->sendMessage(text: implode("\n", $lines));
    }
}
