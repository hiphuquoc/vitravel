<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StayTaxon extends Model
{
    protected $fillable = [
        'group', 'code', 'stay_area_id', 'service_category_id',
        'filter_url', 'source', 'sort', 'is_active', 'create_page', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
            'create_page' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StayTaxonTranslation::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(StayArea::class, 'stay_area_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(StayProperty::class, 'stay_property_taxon');
    }

    public function translation(?string $locale = null): ?StayTaxonTranslation
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
        return (string) ($this->translation($locale)?->name ?: $this->code);
    }
}
