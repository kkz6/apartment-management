<?php

use Illuminate\Support\Facades\Route;
use Modules\Telegram\Http\Controllers\WebhookController;

Route::post('telegram/webhook', [WebhookController::class, 'handle'])->name('telegram.webhook');
