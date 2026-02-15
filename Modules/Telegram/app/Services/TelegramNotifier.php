<?php

namespace Modules\Telegram\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class TelegramNotifier
{
    public function __construct(
        private Nutgram $bot,
    ) {}

    public function notifyAdmins(string $message): void
    {
        $users = User::whereNotNull('telegram_chat_id')->get();

        foreach ($users as $user) {
            try {
                $this->bot->sendMessage(
                    text: $message,
                    chat_id: $user->telegram_chat_id,
                );
            } catch (\Throwable $e) {
                Log::warning("Failed to send Telegram notification to user {$user->id}: {$e->getMessage()}");
            }
        }
    }
}
