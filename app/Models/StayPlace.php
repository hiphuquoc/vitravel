<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayPlace extends Model
{
    protected $fillable = [
        'category',
        'icon',
        'lat',
        'lng',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StayPlaceTranslation::class);
    }

    public function translation(?string $locale = null): ?StayPlaceTranslation
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
        return $this->belongsToMany(Service::class, 'stay_place_service')
            ->withPivot(['distance_meters', 'sort']);
    }
}
