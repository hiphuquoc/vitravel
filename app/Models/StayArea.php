<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayArea extends Model
{
    public const LEVEL_COUNTRY = 'country';

    public const LEVEL_REGION = 'region';

    public const LEVEL_DESTINATION = 'destination';

    public const LEVEL_ZONE = 'zone';

    protected $fillable = [
        'parent_id', 'slug', 'level', 'country_code',
        'booking_dest_id', 'booking_dest_type',
        'lat', 'lng', 'bbox', 'radius_meters', 'aliases',
        'list_url', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'float',
            'lng' => 'float',
            'bbox' => 'array',
            'aliases' => 'array',
            'radius_meters' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StayAreaTranslation::class);
    }

    public function relatedAreas(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'stay_area_related', 'stay_area_id', 'related_area_id');
    }

    public function translation(?string $locale = null): ?StayAreaTranslation
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

    public function displayName(?string $locale = null): string
    {
        return (string) ($this->translation($locale)?->name ?: $this->slug);
    }

    /** @return list<int> */
    public function descendantIds(bool $includeSelf = true): array
    {
        $ids = $includeSelf ? [(int) $this->id] : [];
        foreach ($this->children()->get() as $child) {
            $ids = array_merge($ids, $child->descendantIds(true));
        }

        return array_values(array_unique($ids));
    }
}
