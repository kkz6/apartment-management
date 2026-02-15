<?php

namespace Modules\Telegram\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use SergiX44\Nutgram\Nutgram;

class WebhookController extends Controller
{
    public function handle(Nutgram $bot): Response
    {
        $bot->run();

        return response('ok');
    }
}
