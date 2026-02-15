<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Apartment\Database\Factories\MaintenanceSlabFactory;

class MaintenanceSlab extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_type',
        'amount',
        'effective_from',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
    ];

    protected static function newFactory(): MaintenanceSlabFactory
    {
        return MaintenanceSlabFactory::new();
    }

    public function scopeActiveFor(Builder $query, string $flatType, ?string $date = null): Builder
    {
        $date ??= now()->toDateString();

        return $query->where('flat_type', $flatType)
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from');
    }

    public static function currentRate(string $flatType, ?string $date = null): ?float
    {
        return static::activeFor($flatType, $date)->value('amount');
    }
}
