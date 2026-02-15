<?php

namespace Modules\Telegram\Observers;

use Modules\Import\Models\Upload;
use Modules\Telegram\Services\TelegramNotifier;

class UploadObserver
{
    public function updated(Upload $upload): void
    {
        if (! config('services.telegram.bot_token')) {
            return;
        }

        if (! $upload->wasChanged('status')) {
            return;
        }

        if ($upload->status === 'processed') {
            $count = $upload->parsedTransactions()->count();
            $type = str_replace('_', ' ', $upload->type);

            app(TelegramNotifier::class)->notifyAdmins(
                "📄 Upload processed: {$type} with {$count} transactions"
            );
        }

        if ($upload->status === 'failed') {
            $type = str_replace('_', ' ', $upload->type);

            app(TelegramNotifier::class)->notifyAdmins(
                "❌ Upload failed: {$type} (ID: {$upload->id})"
            );
        }
    }
}
