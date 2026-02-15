<?php

namespace Modules\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;

class StartCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $chatId = $bot->chatId();
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $bot->sendMessage(text: "Welcome back, {$user->name}! Use /help to see available commands.");

            return;
        }

        $bot->sendMessage(text: implode("\n", [
            "Welcome to Apartment Management Bot!",
            "",
            "To get started, link your admin account:",
            "/link {your-email}",
            "",
            "Example: /link admin@example.com",
        ]));
    }
}
