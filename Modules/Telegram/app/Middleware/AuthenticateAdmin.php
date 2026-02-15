<?php

namespace Modules\Telegram\Middleware;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;

class AuthenticateAdmin
{
    public function __invoke(Nutgram $bot, $next): void
    {
        $chatId = $bot->chatId();
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            $bot->sendMessage(text: "You are not linked. Use /link {your-email} to connect your account.");

            return;
        }

        $bot->set('user', $user);
        $next($bot);
    }
}
