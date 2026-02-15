<?php

namespace Modules\Telegram\Handlers;

use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Billing\Support\BillingQuarter;
use SergiX44\Nutgram\Nutgram;

class SummaryCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $quarter = BillingQuarter::current();
        $quarterRange = BillingQuarter::dateRange($quarter);

        $collected = (float) Payment::whereBetween('paid_date', [$quarterRange['start'], $quarterRange['end']])
            ->sum('amount');

        $pendingDues = (float) Charge::where('status', '!=', 'paid')->sum('amount')
            - (float) Payment::whereHas('charge', fn ($q) => $q->where('status', '!=', 'paid'))->sum('amount');

        $expenses = (float) Expense::whereBetween('paid_date', [$quarterRange['start'], $quarterRange['end']])
            ->sum('amount');

        $label = BillingQuarter::label($quarter);

        $bot->sendMessage(text: implode("\n", [
            "📊 Summary for {$label}",
            "",
            "Collected: ₹" . number_format($collected, 2),
            "Pending dues: ₹" . number_format(max(0, $pendingDues), 2),
            "Expenses: ₹" . number_format($expenses, 2),
        ]));
    }
}
