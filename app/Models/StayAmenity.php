<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StayAmenity extends Model
{
    protected $fillable = [
        'group_key',
        'icon',
        'is_highlight',
        'sort',
    ];

    protected $casts = [
        'is_highlight' => 'boolean',
        'sort' => 'integer',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(StayAmenityTranslation::class);
    }

    public function translation(?string $locale = null): ?StayAmenityTranslation
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

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'stay_amenity_service')
            ->withPivot(['is_popular', 'sort']);
    }

    public function serviceOptions(): BelongsToMany
    {
        return $this->belongsToMany(ServiceOption::class, 'stay_amenity_service_option')
            ->withPivot(['sort']);
    }
}
