<?php

use App\Models\User;
use Modules\Telegram\Services\TelegramNotifier;
use SergiX44\Nutgram\Testing\FakeNutgram;

it('sends notifications to all linked admin users', function () {
    User::factory()->create(['telegram_chat_id' => 11111]);
    User::factory()->create(['telegram_chat_id' => 22222]);
    User::factory()->create(['telegram_chat_id' => null]);

    $bot = FakeNutgram::instance();
    $notifier = new TelegramNotifier($bot);

    $notifier->notifyAdmins('Test notification');

    $bot->assertCalled('sendMessage', times: 2);
});
