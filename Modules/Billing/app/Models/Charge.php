<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Apartment\Models\Unit;
use Modules\Billing\Database\Factories\ChargeFactory;

class Charge extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'type',
        'description',
        'amount',
        'billing_month',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    protected static function newFactory(): ChargeFactory
    {
        return ChargeFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balanceAmount(): float
    {
        return (float) $this->amount - $this->paidAmount();
    }

    public function updateStatus(): void
    {
        $paid = $this->paidAmount();

        $this->status = match (true) {
            $paid >= (float) $this->amount => 'paid',
            $paid > 0 => 'partial',
            default => 'pending',
        };

        $this->save();
    }
}
