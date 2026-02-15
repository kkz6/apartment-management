<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Payment;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = $payment->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($billingMonth);
    }

    public function updated(Payment $payment): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $billingMonth = $payment->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($billingMonth);
    }
}
