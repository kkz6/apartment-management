<?php

namespace Modules\Telegram\Handlers;

use Modules\Billing\Models\Expense;
use Modules\Billing\Support\BillingQuarter;
use SergiX44\Nutgram\Nutgram;

class ExpensesCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $quarter = BillingQuarter::current();
        $quarterRange = BillingQuarter::dateRange($quarter);

        $expenses = Expense::whereBetween('paid_date', [$quarterRange['start'], $quarterRange['end']])
            ->orderByDesc('paid_date')
            ->get();

        if ($expenses->isEmpty()) {
            $bot->sendMessage(text: "No expenses for " . BillingQuarter::label($quarter));

            return;
        }

        $label = BillingQuarter::label($quarter);
        $lines = ["💸 Expenses for {$label}", ""];

        foreach ($expenses as $expense) {
            $date = $expense->paid_date->format('d-m-Y');
            $lines[] = "₹" . number_format($expense->amount, 2) . " | {$expense->description} | {$date}";
        }

        $total = $expenses->sum('amount');
        $lines[] = "";
        $lines[] = "Total: ₹" . number_format($total, 2);

        $bot->sendMessage(text: implode("\n", $lines));
    }
}
