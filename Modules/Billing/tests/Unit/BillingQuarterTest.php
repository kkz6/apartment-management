<?php

use Carbon\Carbon;
use Modules\Billing\Support\BillingQuarter;

it('validates correct quarter formats', function () {
    expect(BillingQuarter::isValid('2026-Q1'))->toBeTrue()
        ->and(BillingQuarter::isValid('2025-Q4'))->toBeTrue()
        ->and(BillingQuarter::isValid('2026-Q0'))->toBeFalse()
        ->and(BillingQuarter::isValid('2026-Q5'))->toBeFalse()
        ->and(BillingQuarter::isValid('2026-02'))->toBeFalse()
        ->and(BillingQuarter::isValid('Q1-2026'))->toBeFalse()
        ->and(BillingQuarter::isValid(''))->toBeFalse();
});

it('returns current quarter', function () {
    Carbon::setTestNow(Carbon::parse('2026-02-15'));

    expect(BillingQuarter::current())->toBe('2026-Q1');

    Carbon::setTestNow();
});

it('returns correct date ranges for all quarters', function () {
    $q1 = BillingQuarter::dateRange('2026-Q1');
    expect($q1['start']->format('Y-m-d'))->toBe('2026-01-01')
        ->and($q1['end']->format('Y-m-d'))->toBe('2026-03-31');

    $q2 = BillingQuarter::dateRange('2026-Q2');
    expect($q2['start']->format('Y-m-d'))->toBe('2026-04-01')
        ->and($q2['end']->format('Y-m-d'))->toBe('2026-06-30');

    $q3 = BillingQuarter::dateRange('2026-Q3');
    expect($q3['start']->format('Y-m-d'))->toBe('2026-07-01')
        ->and($q3['end']->format('Y-m-d'))->toBe('2026-09-30');

    $q4 = BillingQuarter::dateRange('2026-Q4');
    expect($q4['start']->format('Y-m-d'))->toBe('2026-10-01')
        ->and($q4['end']->format('Y-m-d'))->toBe('2026-12-31');
});

it('formats quarter labels correctly', function () {
    expect(BillingQuarter::label('2026-Q1'))->toBe('Q1 2026')
        ->and(BillingQuarter::label('2025-Q4'))->toBe('Q4 2025');
});

it('derives quarter from date', function () {
    expect(BillingQuarter::fromDate(Carbon::parse('2026-01-15')))->toBe('2026-Q1')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-03-31')))->toBe('2026-Q1')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-04-01')))->toBe('2026-Q2')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-06-30')))->toBe('2026-Q2')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-07-01')))->toBe('2026-Q3')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-10-01')))->toBe('2026-Q4')
        ->and(BillingQuarter::fromDate(Carbon::parse('2026-12-31')))->toBe('2026-Q4');
});
