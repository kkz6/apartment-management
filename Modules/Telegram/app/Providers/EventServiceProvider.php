<?php

namespace Modules\Telegram\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Billing\Models\Payment;
use Modules\Import\Models\Upload;
use Modules\Telegram\Observers\PaymentObserver;
use Modules\Telegram\Observers\UploadObserver;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        if (config('services.telegram.bot_token')) {
            Payment::observe(PaymentObserver::class);
            Upload::observe(UploadObserver::class);
        }
    }
}
