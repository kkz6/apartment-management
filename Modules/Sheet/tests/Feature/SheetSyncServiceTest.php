<?php

use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Modules\Apartment\Models\Resident;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Models\Charge;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Sheet\Jobs\SyncToGoogleSheet;
use Modules\Sheet\Services\SheetSyncService;
use Revolution\Google\Sheets\Contracts\Factory;

function mockSheets(?Closure $updateCallback = null): void
{
    $mock = Mockery::mock(Factory::class);
    $mock->shouldReceive('spreadsheet')->andReturn($mock);
    $mock->shouldReceive('sheet')->andReturn($mock);

    if ($updateCallback) {
        $mock->shouldReceive('update')->once()->withArgs($updateCallback);
    } else {
        $mock->shouldReceive('update')->andReturn($mock);
    }

    app()->instance(Factory::class, $mock);

    mockGoogleSheetsService();
}

function mockGoogleSheetsService(): void
{
    $valuesMock = Mockery::mock(\Google\Service\Sheets\Resource\SpreadsheetsValues::class);
    $valuesMock->shouldReceive('get')->andReturn([]);

    $serviceMock = Mockery::mock(\Google\Service\Sheets::class);
    $serviceMock->spreadsheets_values = $valuesMock;

    app()->instance(\Google\Service\Sheets::class, $serviceMock);
}

beforeEach(function () {
    Queue::fake();
    config(['services.google.sheet_id' => 'test-sheet-id']);
});

it('builds monthly tab data with payments and expenses in new format', function () {
    $user = User::factory()->create(['name' => 'Karthick K']);
    $unit = Unit::factory()->create(['flat_number' => '101']);
    Resident::factory()->create(['unit_id' => $unit->id, 'name' => 'Karthick']);
    $charge = Charge::factory()->create([
        'unit_id' => $unit->id,
        'type' => 'maintenance',
        'description' => 'Q1 2026 Maintenance',
        'billing_month' => '2026-Q1',
    ]);

    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'amount' => 2000.00,
        'paid_date' => '2026-02-15',
        'source' => 'gpay',
        'added_by' => $user->id,
    ]);

    Expense::factory()->create([
        'description' => 'Rangoli powder',
        'amount' => 200.00,
        'paid_date' => '2026-02-10',
        'category' => 'maintenance',
        'added_by' => $user->id,
    ]);

    mockSheets(function ($rows) {
        // Header row
        if ($rows[0] !== ['Date', 'Type', 'Category', 'Description', 'Amount', 'Receipt', 'Added By', 'Timestamp']) {
            return false;
        }

        // Totals row
        if ($rows[1][0] !== 'Totals') {
            return false;
        }

        // 2 data rows + header + totals = 4 rows
        if (count($rows) !== 4) {
            return false;
        }

        // Expense row comes first (Feb 10 before Feb 15)
        $expenseRow = $rows[2];
        if ($expenseRow[0] !== '2026-02-10' || $expenseRow[1] !== 'expense' || $expenseRow[2] !== 'maintenance') {
            return false;
        }

        // Payment row
        $paymentRow = $rows[3];
        if ($paymentRow[0] !== '2026-02-15' || $paymentRow[1] !== 'income' || $paymentRow[2] !== 'maintenance') {
            return false;
        }

        if ($paymentRow[6] !== 'Karthick K') {
            return false;
        }

        return true;
    });

    $service = new SheetSyncService;
    $service->syncMonthlyTab('2026-02');
});

it('calculates totals row correctly', function () {
    Payment::factory()->create([
        'amount' => 3000.00,
        'paid_date' => '2026-03-15',
    ]);

    Payment::factory()->create([
        'amount' => 2000.00,
        'paid_date' => '2026-03-20',
    ]);

    Expense::factory()->create([
        'amount' => 500.00,
        'paid_date' => '2026-03-10',
    ]);

    mockSheets(function ($rows) {
        $totalsRow = $rows[1];

        return $totalsRow[0] === 'Totals'
            && $totalsRow[3] === 'Net Balance: ₹4,500'
            && $totalsRow[4] === 'Income: ₹5,000 / Expenses: ₹500';
    });

    $service = new SheetSyncService;
    $service->syncMonthlyTab('2026-03');
});

it('generates correct month tab name format', function () {
    $service = new SheetSyncService;

    expect($service->monthTabName('2026-01'))->toBe('Jan 2026')
        ->and($service->monthTabName('2025-12'))->toBe('Dec 2025')
        ->and($service->monthTabName('2025-05'))->toBe('May 2025');
});

it('builds summary tab data with unit charges and payments', function () {
    $unit = Unit::factory()->create(['flat_number' => '201']);
    Resident::factory()->create(['unit_id' => $unit->id, 'name' => 'Ravi']);

    $charge = Charge::factory()->create([
        'unit_id' => $unit->id,
        'type' => 'maintenance',
        'billing_month' => '2026-Q1',
        'amount' => 3000.00,
    ]);

    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'amount' => 3000.00,
        'paid_date' => '2026-01-15',
    ]);

    mockSheets(function ($rows) {
        return count($rows) === 2
            && $rows[0][0] === 'Unit'
            && $rows[1][0] === '201'
            && $rows[1][1] === 'Ravi';
    });

    $service = new SheetSyncService;
    $service->syncSummaryTab();
});

it('dispatches sync job with month when a payment is created', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create(['unit_id' => $unit->id, 'billing_month' => '2026-Q1']);

    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'paid_date' => '2026-02-15',
    ]);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->month === '2026-02';
    });
});

it('dispatches sync job when a charge is created', function () {
    $unit = Unit::factory()->create();

    Charge::factory()->create([
        'unit_id' => $unit->id,
        'billing_month' => '2026-Q1',
    ]);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->month === null;
    });
});

it('dispatches sync job with month when an expense is created', function () {
    Expense::factory()->create(['paid_date' => '2026-02-10']);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->month === '2026-02';
    });
});

it('syncs both monthly and summary tabs when month is provided', function () {
    $mock = Mockery::mock(Factory::class);
    $mock->shouldReceive('spreadsheet')->andReturn($mock);
    $mock->shouldReceive('sheet')->andReturn($mock);
    $mock->shouldReceive('update')->twice();

    app()->instance(Factory::class, $mock);
    mockGoogleSheetsService();

    $service = new SheetSyncService;

    $job = new SyncToGoogleSheet('2026-02');
    $job->handle($service);
});

it('syncs only summary tab when no month is provided', function () {
    $mock = Mockery::mock(Factory::class);
    $mock->shouldReceive('spreadsheet')->andReturn($mock);
    $mock->shouldReceive('sheet')->with('Summary')->andReturn($mock);
    $mock->shouldReceive('update')->once();

    app()->instance(Factory::class, $mock);
    mockGoogleSheetsService();

    $service = new SheetSyncService;

    $job = new SyncToGoogleSheet;
    $job->handle($service);
});

it('handles empty month with no data gracefully', function () {
    mockSheets(function ($rows) {
        return count($rows) === 2
            && $rows[0][0] === 'Date'
            && $rows[1][0] === 'Totals'
            && $rows[1][3] === 'Net Balance: ₹0'
            && $rows[1][4] === 'Income: ₹0 / Expenses: ₹0';
    });

    $service = new SheetSyncService;
    $service->syncMonthlyTab('2026-06');
});

it('shows empty added_by for system-created records', function () {
    Payment::factory()->create([
        'amount' => 1000.00,
        'paid_date' => '2026-04-15',
        'added_by' => null,
    ]);

    mockSheets(function ($rows) {
        return count($rows) === 3
            && $rows[2][6] === '';
    });

    $service = new SheetSyncService;
    $service->syncMonthlyTab('2026-04');
});
