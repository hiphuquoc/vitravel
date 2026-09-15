<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayAreaTranslation extends Model
{
    protected $fillable = [
        'stay_area_id',
        'language_id',
        'name',
        'slug',
        'intro',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(StayArea::class, 'stay_area_id');
    }
}
