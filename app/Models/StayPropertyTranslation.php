<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayPropertyTranslation extends Model
{
    protected $fillable = [
        'stay_property_id',
        'language_id',
        'title',
        'location_label',
        'content',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(StayProperty::class, 'stay_property_id');
    }
}
