<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayPlaceAlias extends Model
{
    protected $fillable = [
        'stay_place_id',
        'alias',
        'normalized',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(StayPlace::class, 'stay_place_id');
    }
}
