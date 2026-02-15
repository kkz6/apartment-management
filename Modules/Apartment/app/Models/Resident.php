<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Apartment\Database\Factories\ResidentFactory;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'phone',
        'email',
        'is_owner',
        'gpay_name',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
    ];

    protected static function newFactory(): ResidentFactory
    {
        return ResidentFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
