<?php

use App\Models\User;
use Modules\Apartment\Models\MaintenanceSlab;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Payment;
use Modules\Billing\Support\BillingQuarter;
use Modules\Telegram\Handlers\BalanceCommand;
use Modules\Telegram\Handlers\BalancesCommand;
use Modules\Telegram\Handlers\ChargesCommand;
use Modules\Telegram\Handlers\ExpensesCommand;
use Modules\Telegram\Handlers\GenerateCommand;
use Modules\Telegram\Handlers\HelpCommand;
use Modules\Telegram\Handlers\LinkCommand;
use Modules\Telegram\Handlers\PayCommand;
use Modules\Telegram\Handlers\PaymentsCommand;
use Modules\Telegram\Handlers\PendingCommand;
use Modules\Telegram\Handlers\StartCommand;
use Modules\Telegram\Handlers\SummaryCommand;
use Modules\Telegram\Middleware\AuthenticateAdmin;
use SergiX44\Nutgram\Testing\FakeNutgram;

function createBot(): FakeNutgram
{
    return FakeNutgram::instance();
}

// Start command

it('responds to /start command', function () {
    $bot = createBot();
    $bot->onCommand('start', StartCommand::class);

    $bot->hearText('/start')->reply();

    $bot->assertReply('sendMessage');
});

// Link command

it('links user account via /link with valid email', function () {
    $user = User::factory()->create(['email' => 'admin@test.com']);

    $bot = createBot();
    $bot->onText('/link(.*)', LinkCommand::class);

    $bot->hearText('/link admin@test.com')->reply();

    $bot->assertReply('sendMessage');

    $user->refresh();

    expect($user->telegram_chat_id)->not->toBeNull();
});

it('rejects /link with unknown email', function () {
    $bot = createBot();
    $bot->onText('/link(.*)', LinkCommand::class);

    $bot->hearText('/link unknown@test.com')->reply();

    $bot->assertReplyText('No account found with that email.');
});

it('rejects /link without email', function () {
    $bot = createBot();
    $bot->onText('/link(.*)', LinkCommand::class);

    $bot->hearText('/link')->reply();

    $bot->assertReply('sendMessage');
});

// Auth middleware

it('blocks unauthenticated users from protected commands', function () {
    $bot = createBot();
    $bot->onCommand('help', HelpCommand::class)
        ->middleware(AuthenticateAdmin::class);

    $bot->hearText('/help')->reply();

    $bot->assertReplyText("You are not linked. Use /link {your-email} to connect your account.");
});

// Summary command

it('shows quarter summary via /summary', function () {
    $unit = Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    $charge = Charge::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 6000,
        'billing_month' => BillingQuarter::current(),
        'status' => 'pending',
    ]);
    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'amount' => 3000,
        'paid_date' => now(),
    ]);

    $bot = createBot();
    $bot->onCommand('summary', SummaryCommand::class);

    $bot->hearText('/summary')->reply();

    $bot->assertReply('sendMessage');
});

// Balance command

it('shows balance for a specific unit', function () {
    $unit = Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Charge::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 6000,
        'billing_month' => BillingQuarter::current(),
        'status' => 'pending',
    ]);

    $bot = createBot();
    $bot->onText('/balance\b(.*)', BalanceCommand::class);

    $bot->hearText('/balance 101')->reply();

    $bot->assertReply('sendMessage');
});

it('shows error for unknown unit via /balance', function () {
    $bot = createBot();
    $bot->onText('/balance\b(.*)', BalanceCommand::class);

    $bot->hearText('/balance 999')->reply();

    $bot->assertReplyText('Unit 999 not found.');
});

// Balances command

it('shows all unit balances via /balances', function () {
    Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Unit::factory()->create(['flat_number' => '102', 'flat_type' => '3BHK']);

    $bot = createBot();
    $bot->onCommand('balances', BalancesCommand::class);

    $bot->hearText('/balances')->reply();

    $bot->assertReply('sendMessage');
});

// Charges command

it('shows charges for current quarter via /charges', function () {
    $unit = Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Charge::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 6000,
        'billing_month' => BillingQuarter::current(),
        'status' => 'pending',
    ]);

    $bot = createBot();
    $bot->onText('/charges\b(.*)', ChargesCommand::class);

    $bot->hearText('/charges')->reply();

    $bot->assertReply('sendMessage');
});

it('rejects invalid quarter format via /charges', function () {
    $bot = createBot();
    $bot->onText('/charges\b(.*)', ChargesCommand::class);

    $bot->hearText('/charges invalid')->reply();

    $bot->assertReplyText('Invalid quarter format. Use: /charges 2026-Q1');
});

// Payments command

it('shows recent payments via /payments', function () {
    $unit = Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Payment::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 3000,
        'paid_date' => now(),
        'source' => 'cash',
    ]);

    $bot = createBot();
    $bot->onText('/payments\b(.*)', PaymentsCommand::class);

    $bot->hearText('/payments')->reply();

    $bot->assertReply('sendMessage');
});

// Pay command

it('records a payment via /pay', function () {
    $unit = Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    Charge::factory()->create([
        'unit_id' => $unit->id,
        'amount' => 6000,
        'billing_month' => BillingQuarter::current(),
        'status' => 'pending',
    ]);

    $bot = createBot();
    $bot->onText('/pay\b(.*)', PayCommand::class);

    $bot->hearText('/pay 101 6000 gpay')->reply();

    $bot->assertReply('sendMessage');

    expect(Payment::where('unit_id', $unit->id)->where('amount', 6000)->exists())->toBeTrue();
});

it('shows usage when /pay has missing arguments', function () {
    $bot = createBot();
    $bot->onText('/pay\b(.*)', PayCommand::class);

    $bot->hearText('/pay')->reply();

    $bot->assertReply('sendMessage');
});

it('rejects /pay with invalid amount', function () {
    $bot = createBot();
    $bot->onText('/pay\b(.*)', PayCommand::class);

    $bot->hearText('/pay 101 abc')->reply();

    $bot->assertReplyText('Invalid amount. Must be a positive number.');
});

// Generate command

it('generates charges via /generate', function () {
    Unit::factory()->create(['flat_number' => '101', 'flat_type' => '2BHK']);
    MaintenanceSlab::factory()->create([
        'flat_type' => '2BHK',
        'amount' => 2000,
        'effective_from' => '2025-01-01',
    ]);

    $bot = createBot();
    $bot->onText('/generate\b(.*)', GenerateCommand::class);

    $bot->hearText('/generate ' . BillingQuarter::current())->reply();

    $bot->assertReply('sendMessage');

    expect(Charge::where('type', 'maintenance')->count())->toBe(1);
});

// Pending command

it('shows reconciliation status via /pending', function () {
    $bot = createBot();
    $bot->onCommand('pending', PendingCommand::class);

    $bot->hearText('/pending')->reply();

    $bot->assertReply('sendMessage');
});

// Webhook route

it('returns ok for webhook POST', function () {
    $response = $this->post('/telegram/webhook', [], [
        'Content-Type' => 'application/json',
    ]);

    $response->assertOk();
});
