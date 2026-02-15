<?php

namespace Modules\Apartment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Apartment\Database\Factories\UnitFactory;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'flat_number',
        'flat_type',
        'floor',
        'area_sqft',
    ];

    protected $casts = [
        'area_sqft' => 'decimal:2',
    ];

    protected static function newFactory(): UnitFactory
    {
        return UnitFactory::new();
    }

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
