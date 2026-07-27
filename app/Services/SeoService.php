<?php

namespace App\Services;

use App\Models\Language;
use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function syncSeo(Model $model, string $locale, array $data, ?string $seoType = null): SeoEntry
    {
        $languageId = Language::idByCode($locale);

        if (! $languageId) {
            throw new \InvalidArgumentException("Unknown locale: {$locale}");
        }

        $parentEntry = $this->resolveParentEntry($model, $seoType, $data['parent_id'] ?? null);
        $slug = Str::slug((string) ($data['slug'] ?? ''));
        $context = $this->buildContext($model, $seoType, $data);
        $slugFull = $this->buildSlugFull($seoType ?? '', $locale, $slug, $parentEntry, $context);

        $entry = $model->seoEntry()->firstOrCreate([]);
        $entry->fill([
            'type' => $seoType ?? $entry->type,
            'parent_id' => $parentEntry?->id,
            'level' => $parentEntry ? ($parentEntry->level + 1) : 1,
            'is_indexable' => array_key_exists('is_indexable', $data)
                ? (bool) $data['is_indexable']
                : ($entry->is_indexable ?? true),
            'rating_aggregate_star' => $data['rating_aggregate_star'] ?? $entry->rating_aggregate_star,
            'rating_aggregate_count' => $data['rating_aggregate_count'] ?? $entry->rating_aggregate_count,
            'og_image_id' => $data['og_image_id'] ?? $entry->og_image_id,
        ]);
        $entry->save();

        $translationPayload = [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'seo_title' => $data['seo_title'] ?? ($data['title'] ?? null),
            'seo_description' => $data['seo_description'] ?? ($data['description'] ?? null),
            'keywords' => $data['keywords'] ?? null,
            'slug' => $slug,
            'slug_full' => $slugFull,
            'canonical_url' => $data['canonical_url'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'translation_status' => $data['translation_status'] ?? 'manual',
            'published_at' => $data['published_at'] ?? (($data['status'] ?? '') === 'published' ? now() : null),
        ];

        SeoEntryTranslation::query()->updateOrCreate(
            ['seo_entry_id' => $entry->id, 'language_id' => $languageId],
            $translationPayload,
        );

        return $entry->fresh(['translations']);
    }

    public function buildSlugFull(
        string $seoType,
        string $locale,
        string $slug,
        ?SeoEntry $parentEntry = null,
        array $context = [],
    ): string {
        $parentSlugFull = null;

        if ($parentEntry) {
            $parentTranslation = $parentEntry->translation($locale);
            $parentSlugFull = $parentTranslation?->slug_full;
        }

        $builder = config("seo.slug_full_builders.{$seoType}");

        if (is_callable($builder)) {
            return $builder($locale, $slug, $parentSlugFull, $context);
        }

        if ($parentSlugFull) {
            return rtrim($parentSlugFull, '/').'/'.ltrim($slug, '/');
        }

        return '/'.ltrim($slug, '/');
    }

    public function resolveParentEntry(Model $model, ?string $seoType, mixed $parentId = null): ?SeoEntry
    {
        if ($parentId !== null && $parentId !== '' && (int) $parentId > 0) {
            return SeoEntry::query()->find((int) $parentId);
        }

        $parentRelation = config("seo.types.{$seoType}.parent_relation");

        if (! $parentRelation || ! method_exists($model, $parentRelation)) {
            return null;
        }

        $parentModel = $model->{$parentRelation};

        return $parentModel?->seoEntry;
    }

    /**
     * @return array<string, mixed>
     */
    public function buildContext(Model $model, ?string $seoType, array $data = []): array
    {
        $context = [];

        if (isset($model->country) || (method_exists($model, 'country') && $model->relationLoaded('country'))) {
            $country = $model->country ?? $model->country()->first();
            $context['country_code'] = $country?->code ?? null;
            $context['country_id'] = $country?->id;
        }

        if (isset($data['country_code'])) {
            $context['country_code'] = $data['country_code'];
        }

        return $context;
    }

    public function publicUrl(?SeoEntryTranslation $translation, string $locale): string
    {
        if (! $translation?->slug_full) {
            return '#';
        }

        $defaultLocale = Language::defaultCode() ?? 'vi';
        $prefix = $locale === $defaultLocale ? '' : '/'.$locale;

        return $prefix.'/'.ltrim($translation->slug_full, '/');
    }

    /**
     * @return Collection<int, SeoEntry>
     */
    public function parentOptions(?string $parentType, ?int $excludeId = null): Collection
    {
        if (! $parentType) {
            return collect();
        }

        $query = SeoEntry::query()
            ->with(['translations'])
            ->where(function ($q) use ($parentType) {
                $q->where('type', $parentType)
                    ->orWhere('reference_type', $parentType);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $entries = $query->orderBy('id')->get();

        // Backfill missing type so future lookups stay consistent
        $entries->each(function (SeoEntry $entry) use ($parentType) {
            if ($entry->type !== $parentType) {
                $entry->forceFill(['type' => $parentType])->saveQuietly();
            }
        });

        return $entries;
    }

    /**
     * Ensure parent SEO exists for a related model (e.g. Country of a Package).
     */
    public function ensureSeoFor(Model $model, string $seoType, string $locale, array $data = []): SeoEntry
    {
        $existing = $model->seoEntry;

        if ($existing) {
            if ($existing->type !== $seoType) {
                $existing->forceFill(['type' => $seoType])->saveQuietly();
            }

            return $existing;
        }

        return $this->syncSeo($model, $locale, $data, $seoType);
    }
}
