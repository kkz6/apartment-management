<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Expense;
use Modules\Billing\Support\BillingQuarter;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = BillingQuarter::fromDate($expense->paid_date);

        SyncToGoogleSheet::dispatch($billingMonth);
    }

    public function updated(Expense $expense): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = BillingQuarter::fromDate($expense->paid_date);

        SyncToGoogleSheet::dispatch($billingMonth);
    }
}
