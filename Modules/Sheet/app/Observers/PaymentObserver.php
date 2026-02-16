<?php

namespace Modules\Sheet\Observers;

use Modules\Billing\Models\Payment;
use Modules\Sheet\Jobs\SyncToGoogleSheet;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        $this->dispatch($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->dispatch($payment);
    }

    private function dispatch(Payment $payment): void
    {
        if (! config('services.google.sheet_id')) {
            return;
        }

        $month = $payment->paid_date->format('Y-m');

        SyncToGoogleSheet::dispatch($month);
    }
}
