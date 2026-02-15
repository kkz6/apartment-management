<?php

namespace Modules\Telegram\Console;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;

class SetWebhookCommand extends Command
{
    protected $signature = 'telegram:set-webhook {url?}';

    protected $description = 'Register the Telegram webhook URL';

    public function handle(Nutgram $bot): int
    {
        $url = $this->argument('url') ?: config('services.telegram.webhook_url');

        if (! $url) {
            $this->error('No webhook URL provided. Set TELEGRAM_WEBHOOK_URL in .env or pass it as an argument.');

            return self::FAILURE;
        }

        $bot->setWebhook($url);

        $this->info("Webhook set to: {$url}");

        return self::SUCCESS;
    }
}
