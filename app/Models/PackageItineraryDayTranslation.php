<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItineraryDayTranslation extends Model
{
    protected $fillable = [
        'package_itinerary_day_id', 'language_id', 'title', 'content', 'overnight_at', 'internal_links',
    ];

    protected function casts(): array
    {
        return ['internal_links' => 'array'];
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(PackageItineraryDay::class, 'package_itinerary_day_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
