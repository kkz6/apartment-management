<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Expense;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        $this->dispatch($expense);
    }

    public function updated(Expense $expense): void
    {
        $this->dispatch($expense);
    }

    private function dispatch(Expense $expense): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $month = $expense->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($month);
    }
}
