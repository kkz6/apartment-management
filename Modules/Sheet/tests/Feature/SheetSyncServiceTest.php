<?php

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
}

beforeEach(function () {
    Queue::fake();
    config(['services.google.sheet_id' => 'test-sheet-id']);
});

it('builds quarterly tab data with payments and expenses', function () {
    $unit = Unit::factory()->create(['flat_number' => '101']);
    Resident::factory()->create(['unit_id' => $unit->id, 'name' => 'Karthick']);
    $charge = Charge::factory()->create([
        'unit_id' => $unit->id,
        'type' => 'maintenance',
        'billing_month' => '2026-Q1',
    ]);

    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'amount' => 2000.00,
        'paid_date' => '2026-02-15',
        'source' => 'gpay',
    ]);

    Expense::factory()->create([
        'amount' => 5000.00,
        'paid_date' => '2026-02-10',
        'category' => 'electricity',
    ]);

    mockSheets(function ($rows) {
        return count($rows) === 3
            && $rows[0][0] === 'Date'
            && $rows[1][1] === '101'
            && $rows[1][2] === 'Karthick'
            && $rows[2][4] === 'Expense - electricity';
    });

    $service = new SheetSyncService;
    $service->syncMonthlyTab('2026-Q1');
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

it('dispatches sync job when a payment is created', function () {
    $unit = Unit::factory()->create();
    $charge = Charge::factory()->create(['unit_id' => $unit->id, 'billing_month' => '2026-Q1']);

    Payment::factory()->create([
        'unit_id' => $unit->id,
        'charge_id' => $charge->id,
        'paid_date' => '2026-02-15',
    ]);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->billingMonth === '2026-Q1';
    });
});

it('dispatches sync job when a charge is created', function () {
    $unit = Unit::factory()->create();

    Charge::factory()->create([
        'unit_id' => $unit->id,
        'billing_month' => '2026-Q1',
    ]);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->billingMonth === '2026-Q1';
    });
});

it('dispatches sync job when an expense is created', function () {
    Expense::factory()->create(['paid_date' => '2026-02-10']);

    Queue::assertPushed(SyncToGoogleSheet::class, function ($job) {
        return $job->billingMonth === '2026-Q1';
    });
});

it('generates correct quarter tab name format', function () {
    $service = new SheetSyncService;
    $reflection = new ReflectionMethod($service, 'monthTabName');

    expect($reflection->invoke($service, '2026-Q1'))->toBe('Q1 2026')
        ->and($reflection->invoke($service, '2025-Q4'))->toBe('Q4 2025');
});

it('syncs both monthly and summary tabs when billing quarter is provided', function () {
    $mock = Mockery::mock(Factory::class);
    $mock->shouldReceive('spreadsheet')->andReturn($mock);
    $mock->shouldReceive('sheet')->andReturn($mock);
    $mock->shouldReceive('update')->twice();

    app()->instance(Factory::class, $mock);

    $service = new SheetSyncService;

    $job = new SyncToGoogleSheet('2026-Q1');
    $job->handle($service);
});

it('syncs only summary tab when no billing quarter is provided', function () {
    $mock = Mockery::mock(Factory::class);
    $mock->shouldReceive('spreadsheet')->andReturn($mock);
    $mock->shouldReceive('sheet')->with('Summary')->andReturn($mock);
    $mock->shouldReceive('update')->once();

    app()->instance(Factory::class, $mock);

    $service = new SheetSyncService;

    $job = new SyncToGoogleSheet;
    $job->handle($service);
});
