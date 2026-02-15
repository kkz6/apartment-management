<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Billing\Database\Factories\ExpenseFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'paid_date',
        'category',
        'source',
        'reference_number',
        'reconciliation_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}
