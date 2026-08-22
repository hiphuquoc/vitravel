<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayAmenityTranslation extends Model
{
    protected $fillable = [
        'stay_amenity_id',
        'language_id',
        'name',
        'slug',
    ];

    public function amenity(): BelongsTo
    {
        return $this->belongsTo(StayAmenity::class, 'stay_amenity_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
