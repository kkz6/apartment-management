<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Charge;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class ChargeObserver
{
    public function created(Charge $charge): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        SyncToGoogleSheet::dispatch($charge->billing_month);
    }

    public function updated(Charge $charge): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        SyncToGoogleSheet::dispatch($charge->billing_month);
    }
}
