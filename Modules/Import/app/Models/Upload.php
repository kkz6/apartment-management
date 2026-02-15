<?php

namespace Modules\Import\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Import\Database\Factories\UploadFactory;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'type',
        'status',
        'processed_at',
        'uploaded_by',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    protected static function newFactory(): UploadFactory
    {
        return UploadFactory::new();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function parsedTransactions(): HasMany
    {
        return $this->hasMany(ParsedTransaction::class);
    }
}
