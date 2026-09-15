<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayProperty extends Model
{
    public const SOURCE_BOOKING = 'booking.com';

    protected $fillable = [
        'source', 'source_hotel_key', 'booking_cc', 'canonical_url', 'source_url',
        'primary_stay_area_id', 'country_code', 'property_type',
        'star_rating', 'rating', 'review_count', 'lat', 'lng',
        'price_from', 'currency', 'status', 'completeness',
        'last_crawled_at', 'attrs',
    ];

    protected function casts(): array
    {
        return [
            'star_rating' => 'integer',
            'rating' => 'float',
            'review_count' => 'integer',
            'lat' => 'float',
            'lng' => 'float',
            'price_from' => 'decimal:2',
            'completeness' => 'array',
            'last_crawled_at' => 'datetime',
            'attrs' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StayPropertyTranslation::class);
    }

    public function primaryArea(): BelongsTo
    {
        return $this->belongsTo(StayArea::class, 'primary_stay_area_id');
    }

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(StayArea::class, 'stay_property_area')
            ->withPivot(['role']);
    }

    public function taxons(): BelongsToMany
    {
        return $this->belongsToMany(StayTaxon::class, 'stay_property_taxon');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'stay_property_id');
    }

    public function translation(?string $locale = null): ?StayPropertyTranslation
    {
        $locale = $locale ?: default_locale();
        $langId = Language::idByCode($locale);
        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $langId)
                ?: $this->translations->first();
        }

        return $this->translations()->where('language_id', $langId)->first()
            ?: $this->translations()->first();
    }
}
