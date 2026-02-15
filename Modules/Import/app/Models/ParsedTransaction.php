<?php

namespace Modules\Import\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Billing\Models\Expense;
use Modules\Billing\Models\Payment;
use Modules\Import\Database\Factories\ParsedTransactionFactory;

class ParsedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'upload_id',
        'raw_text',
        'sender_name',
        'amount',
        'date',
        'direction',
        'fingerprint',
        'match_type',
        'matched_payment_id',
        'matched_expense_id',
        'reconciliation_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    protected static function newFactory(): ParsedTransactionFactory
    {
        return ParsedTransactionFactory::new();
    }

    public static function generateFingerprint(float $amount, string $date, ?string $name = null): string
    {
        $normalized = strtolower(trim($name ?? ''));

        return hash('xxh128', "{$amount}|{$date}|{$normalized}");
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function matchedPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'matched_payment_id');
    }

    public function matchedExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'matched_expense_id');
    }
}
