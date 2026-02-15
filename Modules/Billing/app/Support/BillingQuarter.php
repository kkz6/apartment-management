<?php

namespace Modules\Billing\Support;

use Carbon\Carbon;

class BillingQuarter
{
    public static function isValid(string $quarter): bool
    {
        return (bool) preg_match('/^\d{4}-Q[1-4]$/', $quarter);
    }

    public static function current(): string
    {
        return self::fromDate(now());
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    public static function dateRange(string $quarter): array
    {
        [$year, $q] = self::parse($quarter);

        $startMonth = ($q - 1) * 3 + 1;

        $start = Carbon::create($year, $startMonth, 1)->startOfDay();
        $end = $start->copy()->addMonths(2)->endOfMonth()->endOfDay();

        return ['start' => $start, 'end' => $end];
    }

    public static function label(string $quarter): string
    {
        [$year, $q] = self::parse($quarter);

        return "Q{$q} {$year}";
    }

    public static function fromDate(Carbon $date): string
    {
        $q = (int) ceil($date->month / 3);

        return "{$date->year}-Q{$q}";
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function parse(string $quarter): array
    {
        $year = (int) substr($quarter, 0, 4);
        $q = (int) substr($quarter, 6, 1);

        return [$year, $q];
    }
}
