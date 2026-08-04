<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoEntry extends Model
{
    /** @var array<string, mixed> */
    protected $attributes = [
        'is_indexable' => true,
    ];

    protected $fillable = [
        'reference_type', 'reference_id', 'type', 'parent_id', 'level',
        'og_image_id', 'rating_aggregate_star', 'rating_aggregate_count', 'is_indexable',
    ];

    protected function casts(): array
    {
        return [
            'rating_aggregate_star' => 'decimal:2',
            'rating_aggregate_count' => 'integer',
            'is_indexable' => 'boolean',
            'level' => 'integer',
        ];
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'og_image_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SeoEntryTranslation::class);
    }

    public function translation(?string $locale = null): ?SeoEntryTranslation
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return \App\Support\LocaleContent::firstTranslation($this->translations, $locale);
        }

        $ids = Language::contentLanguageIdChain($locale);
        if ($ids === []) {
            return null;
        }

        $rows = $this->translations()->whereIn('language_id', $ids)->get();

        return \App\Support\LocaleContent::firstTranslation($rows, $locale);
    }

    /**
     * Bản dịch SEO đúng locale — không fallback EN/VI (dùng cho slug_full / URL).
     */
    public function translationExact(?string $locale = null): ?SeoEntryTranslation
    {
        $locale = $locale ?: app()->getLocale();
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return null;
        }

        if ($this->relationLoaded('translations')) {
            return $this->translations->firstWhere('language_id', $languageId);
        }

        return $this->translations()->where('language_id', $languageId)->first();
    }
}
