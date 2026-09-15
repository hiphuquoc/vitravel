<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayAmenityAlias extends Model
{
    protected $fillable = [
        'stay_amenity_id',
        'alias',
        'normalized',
    ];

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(StayAmenity::class, 'stay_amenity_id');
    }
}
