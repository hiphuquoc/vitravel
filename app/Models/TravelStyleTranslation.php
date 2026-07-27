<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelStyleTranslation extends Model
{
    protected $fillable = ['travel_style_id', 'language_id', 'name', 'slug', 'description'];

    public function travelStyle(): BelongsTo
    {
        return $this->belongsTo(TravelStyle::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
