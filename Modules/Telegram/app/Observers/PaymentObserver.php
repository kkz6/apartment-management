<?php

namespace Modules\Telegram\Observers;

use Modules\Billing\Models\Payment;
use Modules\Telegram\Services\TelegramNotifier;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        if (! config('services.telegram.bot_token')) {
            return;
        }

        $flat = $payment->unit?->flat_number ?? '?';
        $amount = number_format($payment->amount, 2);
        $date = $payment->paid_date->format('d-m-Y');

        app(TelegramNotifier::class)->notifyAdmins(
            "💰 New payment: ₹{$amount} from unit {$flat} via {$payment->source} on {$date}"
        );
    }
}
