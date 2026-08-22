<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayPlaceTranslation extends Model
{
    protected $fillable = [
        'stay_place_id',
        'language_id',
        'name',
        'slug',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(StayPlace::class, 'stay_place_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
