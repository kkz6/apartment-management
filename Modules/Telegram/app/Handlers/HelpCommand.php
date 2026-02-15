<?php

namespace Modules\Telegram\Handlers;

use SergiX44\Nutgram\Nutgram;

class HelpCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage(text: implode("\n", [
            "Available commands:",
            "",
            "/summary - Current quarter overview",
            "/balance {flat} - Balance for a unit (e.g. /balance 101)",
            "/balances - All unit balances",
            "/charges {quarter?} - Charges list (e.g. /charges 2026-Q1)",
            "/payments {flat?} - Recent payments",
            "/expenses - Current quarter expenses",
            "/pending - Unmatched transaction count",
            "",
            "/pay {flat} {amount} {source?} - Record a payment",
            "/generate {quarter?} - Generate maintenance charges",
            "",
            "/help - Show this message",
        ]));
    }
}
