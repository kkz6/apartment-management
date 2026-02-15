<?php

namespace Modules\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;

class LinkCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $text = $bot->message()?->text ?? '';
        $parts = explode(' ', $text, 2);
        $email = trim($parts[1] ?? '');

        if (! $email) {
            $bot->sendMessage(text: "Please provide your email: /link admin@example.com");

            return;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $bot->sendMessage(text: "No account found with that email.");

            return;
        }

        if ($user->telegram_chat_id && $user->telegram_chat_id !== $bot->chatId()) {
            $bot->sendMessage(text: "This account is already linked to another Telegram chat.");

            return;
        }

        $user->update(['telegram_chat_id' => $bot->chatId()]);

        $bot->sendMessage(text: "Linked successfully! Welcome, {$user->name}.\n\nUse /help to see available commands.");
    }
}
