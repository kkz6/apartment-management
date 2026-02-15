<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Expense;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = $expense->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($billingMonth);
    }

    public function updated(Expense $expense): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = $expense->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($billingMonth);
    }
}
