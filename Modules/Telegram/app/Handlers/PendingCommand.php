<?php

namespace Modules\Telegram\Handlers;

use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Models\ParsedTransaction;
use SergiX44\Nutgram\Nutgram;

class PendingCommand
{
    public function __invoke(Nutgram $bot): void
    {
        $pendingVerification = Payment::where('reconciliation_status', 'pending_verification')->count()
            + Expense::where('reconciliation_status', 'pending_verification')->count();

        $bankVerified = Payment::where('reconciliation_status', 'bank_verified')->count()
            + Expense::where('reconciliation_status', 'bank_verified')->count();

        $unmatched = ParsedTransaction::where('reconciliation_status', 'unmatched')->count();

        $bot->sendMessage(text: implode("\n", [
            "🔍 Reconciliation Status",
            "",
            "Pending verification: {$pendingVerification}",
            "Bank verified: {$bankVerified}",
            "Unmatched transactions: {$unmatched}",
        ]));
    }
}
