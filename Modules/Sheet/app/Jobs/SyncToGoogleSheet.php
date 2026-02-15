<?php

namespace Modules\Sheet\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Sheet\Services\SheetSyncService;

class SyncToGoogleSheet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?string $billingMonth = null,
    ) {}

    public function handle(SheetSyncService $service): void
    {
        if ($this->billingMonth) {
            $service->syncMonthlyTab($this->billingMonth);
        }

        $service->syncSummaryTab();
    }
}
