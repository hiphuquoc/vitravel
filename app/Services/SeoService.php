<?php

namespace App\Services;

use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\Country;
use App\Models\CruiseType;
use App\Models\Language;
use App\Models\Package;
use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use App\Models\SeoRedirect;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeoService
{
    /**
     * Sync SEO hub + translation.
     * Hitour pattern: chọn parent → level = parent.level+1 → slug_full = f(parent.slug_full, slug, type).
     *
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
        $context = $this->buildContext($model, $seoType, $data, $locale);

        // Cho phép seed/admin ghi đè slug_full (vd bài viết: hub/country/slug)
        if (filled($data['slug_full'] ?? null)) {
            $slugFull = $this->normalizeSlugFull((string) $data['slug_full']);
        } else {
            $slugFull = $this->buildSlugFull($seoType ?? '', $locale, $slug, $parentEntry, $context);
        }

        $entry = $model->seoEntry()->firstOrCreate([]);
        $existingTranslation = SeoEntryTranslation::query()
            ->where('seo_entry_id', $entry->id)
            ->where('language_id', $languageId)
            ->first();

        $this->assertSlugFullUnique($languageId, $slugFull, $existingTranslation?->id);

        $oldSlugFull = $existingTranslation?->slug_full;

        $resolvedType = $seoType
            ?? (filled($entry->type) ? (string) $entry->type : null)
            ?? (filled($entry->reference_type) ? (string) $entry->reference_type : null);

        $entry->fill([
            'type' => $resolvedType,
            'parent_id' => $parentEntry?->id,
            'level' => $parentEntry ? ((int) $parentEntry->level + 1) : 1,
            'is_indexable' => array_key_exists('is_indexable', $data)
                ? (bool) $data['is_indexable']
                : ($entry->is_indexable ?? true),
            'rating_aggregate_star' => $data['rating_aggregate_star'] ?? $entry->rating_aggregate_star,
            'rating_aggregate_count' => $data['rating_aggregate_count'] ?? $entry->rating_aggregate_count,
            'og_image_id' => $data['og_image_id'] ?? $entry->og_image_id,
        ]);
        $entry->save();

        if (filled($oldSlugFull) && $oldSlugFull !== $slugFull) {
            $this->createRedirect301($oldSlugFull, $slugFull, $languageId);
        }

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

        $entry = $entry->fresh(['translations', 'children.translations']);

        // Khi đổi slug/parent của cha → cascade cập nhật slug_full mọi con (cùng locale)
        $this->cascadeSlugFullChildren($entry, $locale);

        return $entry->fresh(['translations']);
    }

    public function buildSlugFull(
        string $seoType,
        string $locale,
        string $slug,
        ?SeoEntry $parentEntry = null,
        array $context = [],
    ): string {
        $slug = ltrim(Str::slug($slug !== '' ? $slug : 'page'), '/');

        $parentSlugFull = null;
        if ($parentEntry) {
            $parentTranslation = $parentEntry->translation($locale);
            $parentSlugFull = $parentTranslation?->slug_full;
        }

        // Hitour duy nhất: có cha → nối; không cha (hub/root) → /{slug}
        // Không hardcode /tours|/cruises|/cam-nang theo type.
        if (filled($parentSlugFull)) {
            return $this->normalizeSlugFull(rtrim((string) $parentSlugFull, '/').'/'.$slug);
        }

        $defaultSlug = (string) (config("seo.types.{$seoType}.default_slug") ?? '');
        if ($slug === '' && $defaultSlug !== '') {
            $slug = $defaultSlug;
        }

        return $this->normalizeSlugFull('/'.$slug);
    }

    public function normalizeSlugFull(string $slugFull): string
    {
        $slugFull = '/'.trim(str_replace('//', '/', $slugFull), '/');

        return $slugFull === '/' ? '/' : $slugFull;
    }

    /**
     * @throws ValidationException
     */
    public function assertSlugFullUnique(int $languageId, string $slugFull, ?int $ignoreTranslationId = null): void
    {
        $normalized = $this->normalizeSlugFull($slugFull);
        $withoutSlash = ltrim($normalized, '/');

        $query = SeoEntryTranslation::query()
            ->where('language_id', $languageId)
            ->where(function ($q) use ($normalized, $withoutSlash) {
                $q->where('slug_full', $normalized)
                    ->orWhere('slug_full', $withoutSlash);
            });

        if ($ignoreTranslationId) {
            $query->where('id', '!=', $ignoreTranslationId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'seo_slug' => 'Đường dẫn đầy đủ (slug_full) đã tồn tại cho ngôn ngữ này.',
            ]);
        }
    }

    /**
     * Hitour SeoTranslation style: locale prefix nếu không phải default; upsert + chain-update.
     */
    public function createRedirect301(?string $old, ?string $new, int $languageId): void
    {
        if (empty($old) || empty($new) || $old === $new) {
            return;
        }

        try {
            if (! Schema::hasTable('redirect_info')) {
                return;
            }

            $lang = Language::query()->find($languageId);
            $prefix = ($lang && ! $lang->is_default) ? '/'.$lang->code : '';

            $urlOld = $prefix.'/'.ltrim($old, '/');
            $urlNew = $prefix.'/'.ltrim($new, '/');

            $exists = SeoRedirect::query()->where('url_old', $urlOld)->exists();
            if (! $exists) {
                SeoRedirect::query()->create([
                    'url_old' => $urlOld,
                    'url_new' => $urlNew,
                ]);
            } else {
                SeoRedirect::query()->where('url_old', $urlOld)->update(['url_new' => $urlNew]);
            }

            SeoRedirect::query()->where('url_new', $urlOld)->update(['url_new' => $urlNew]);
        } catch (\Throwable $e) {
            Log::warning('SeoService::createRedirect301 failed: '.$e->getMessage(), [
                'slug_old' => $old,
                'slug_new' => $new,
                'language_id' => $languageId,
            ]);
        }
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
    public function buildContext(Model $model, ?string $seoType, array $data = [], ?string $locale = null): array
    {
        $context = [];
        $locale ??= app()->getLocale();

        if (isset($model->country) || (method_exists($model, 'country') && $model->relationLoaded('country'))) {
            $country = $model->country ?? $model->country()->first();
            if ($country) {
                $context['country_code'] = $country->code ?? null;
                $context['country_id'] = $country->id;
                $context['country_slug'] = $country->translation($locale)?->slug
                    ?? $country->translation()?->slug
                    ?? null;
            }
        }

        if (isset($data['country_slug'])) {
            $context['country_slug'] = $data['country_slug'];
        }
        if (isset($data['country_code'])) {
            $context['country_code'] = $data['country_code'];
        }
        if (isset($data['cruise_type'])) {
            $context['cruise_type'] = $data['cruise_type'];
        }

        return $context;
    }

    /**
     * Recursively rebuild slug_full for descendants when parent URL changes.
     */
    public function cascadeSlugFullChildren(SeoEntry $parent, string $locale): void
    {
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return;
        }

        $children = $parent->children()->with('translations')->get();

        foreach ($children as $child) {
            $childTrans = $child->translations->firstWhere('language_id', $languageId);
            if (! $childTrans || ! filled($childTrans->slug)) {
                continue;
            }

            $newFull = $this->buildSlugFull(
                (string) ($child->type ?? ''),
                $locale,
                (string) $childTrans->slug,
                $parent,
            );

            if ($childTrans->slug_full !== $newFull) {
                $oldFull = $childTrans->slug_full;
                $childTrans->forceFill(['slug_full' => $newFull])->save();
                $this->createRedirect301($oldFull, $newFull, $languageId);
            }

            $this->cascadeSlugFullChildren($child, $locale);
        }
    }

    /**
     * Lookup SEO entry by slug_full for public routing.
     */
    public function findBySlugFull(string $path, string $locale, bool $publishedOnly = true): ?SeoEntry
    {
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return null;
        }

        $normalized = $this->normalizeSlugFull($path);
        $withoutSlash = ltrim($normalized, '/');
        if ($withoutSlash === '') {
            return null;
        }

        $query = SeoEntryTranslation::query()
            ->where('language_id', $languageId)
            ->where(function ($q) use ($normalized, $withoutSlash) {
                $q->where('slug_full', $normalized)
                    ->orWhere('slug_full', $withoutSlash);
            });

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        $trans = $query->first();
        if (! $trans) {
            return null;
        }

        $entry = SeoEntry::query()
            ->with(['reference', 'parent.translations', 'translations'])
            ->find($trans->seo_entry_id);

        if ($entry) {
            $entry->setRelation('translation', $trans);
        }

        return $entry;
    }

    public function hubPublicUrl(string $hubKey, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $entry = $this->ensureHub($hubKey, $locale);
        $trans = $entry->translation($locale);

        return $this->publicUrl($trans, $locale);
    }

    /**
     * Path-only (vd `/du-thuyen`, `/du-thuyen/ha-long/...`) cho named public routes.
     * Null nếu không phải SEO route name.
     *
     * @param  array<string, mixed>  $params
     */
    public function namedSeoPath(string $routeName, array $params = [], ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return match ($routeName) {
            'tours.hub' => $this->hubSlugFullPath('tours_hub', $locale),
            'cruises.hub' => $this->hubSlugFullPath('cruises_hub', $locale),
            'guide.index' => $this->hubSlugFullPath('guide_hub', $locale),
            'tours.index' => $this->countrySlugFullPath(
                is_array($params['country'] ?? null) ? null : ($params['country'] ?? null),
                $locale
            ),
            'tours.show' => $this->packageSlugFullPath('tour', $params['slug'] ?? null, $locale)
                ?? $this->composeSlugPath(
                    $this->hubSlugFullPath('tours_hub', $locale),
                    $params['country'] ?? null,
                    $params['slug'] ?? null,
                ),
            'cruises.index' => $this->cruiseTypeSlugFullPath($params['type'] ?? null, $locale),
            'cruises.show' => $this->packageSlugFullPath('cruise', $params['slug'] ?? null, $locale)
                ?? $this->composeSlugPath(
                    $this->hubSlugFullPath('cruises_hub', $locale),
                    $params['type'] ?? null,
                    $params['slug'] ?? null,
                ),
            'guide.country' => $this->guideCountrySlugFullPath($params['country'] ?? null, $locale),
            'guide.show' => $this->articleSlugFullPath($params['slug'] ?? null, $locale)
                ?? $this->composeSlugPath(
                    $this->hubSlugFullPath('guide_hub', $locale),
                    $params['country'] ?? null,
                    $params['slug'] ?? null,
                ),
            default => null,
        };
    }

    protected function composeSlugPath(string $hub, ?string ...$segments): ?string
    {
        $parts = [$hub];
        foreach ($segments as $seg) {
            if (! filled($seg) || is_array($seg)) {
                return null;
            }
            $parts[] = ltrim((string) $seg, '/');
        }

        return $this->normalizeSlugFull(implode('/', $parts));
    }

    public function hubSlugFullPath(string $hubKey, string $locale): string
    {
        $entry = $this->ensureHub($hubKey, $locale);
        $full = $entry->translation($locale)?->slug_full
            ?? '/'.ltrim((string) (config("seo.hubs.{$hubKey}.default_slug") ?? $hubKey), '/');

        return $this->normalizeSlugFull((string) $full);
    }

    protected function countrySlugFullPath(?string $countrySlug, string $locale): ?string
    {
        if (! filled($countrySlug)) {
            return null;
        }

        $full = $this->seoSlugFullByTypeAndSlug('country', $countrySlug, $locale);
        if ($full) {
            return $full;
        }

        $hub = $this->hubSlugFullPath('tours_hub', $locale);

        return $this->normalizeSlugFull(rtrim($hub, '/').'/'.$countrySlug);
    }

    protected function cruiseTypeSlugFullPath(?string $typeSlug, string $locale): ?string
    {
        if (! filled($typeSlug)) {
            return null;
        }

        $type = CruiseType::query()
            ->with(['seoEntry.translations'])
            ->where('slug', $typeSlug)
            ->first();

        $full = $type?->seoEntry?->translation($locale)?->slug_full;
        if ($full) {
            return $this->normalizeSlugFull($full);
        }

        // Fallback: leaf slug on SEO translation (cruise_types.slug may differ)
        $full = $this->seoSlugFullByTypeAndSlug('cruise_type', $typeSlug, $locale);
        if ($full) {
            return $full;
        }

        $hub = $this->hubSlugFullPath('cruises_hub', $locale);

        return $this->normalizeSlugFull(rtrim($hub, '/').'/'.$typeSlug);
    }

    protected function packageSlugFullPath(string $packageType, ?string $slug, string $locale): ?string
    {
        if (! filled($slug)) {
            return null;
        }

        $seoType = $packageType === 'cruise' ? 'package_cruise' : 'package_tour';

        return $this->seoSlugFullByTypeAndSlug($seoType, $slug, $locale);
    }

    protected function guideCountrySlugFullPath(?string $slug, string $locale): ?string
    {
        if (! filled($slug)) {
            return null;
        }

        $full = $this->seoSlugFullByTypeAndSlug('blog_category', $slug, $locale);
        if ($full) {
            return $full;
        }

        $hub = $this->hubSlugFullPath('guide_hub', $locale);

        return $this->normalizeSlugFull(rtrim($hub, '/').'/'.$slug);
    }

    protected function articleSlugFullPath(?string $slug, string $locale): ?string
    {
        if (! filled($slug)) {
            return null;
        }

        return $this->seoSlugFullByTypeAndSlug('article', $slug, $locale);
    }

    /** Tra slug leaf trên seo_entry_translations theo type (slug không nằm trên bảng entity_translations). */
    protected function seoSlugFullByTypeAndSlug(string $seoType, string $slug, string $locale): ?string
    {
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return null;
        }

        $trans = SeoEntryTranslation::query()
            ->where('language_id', $languageId)
            ->where('slug', $slug)
            ->whereHas('seoEntry', function ($q) use ($seoType) {
                $q->where('type', $seoType)
                    ->orWhere(function ($q2) use ($seoType) {
                        // Seed cũ: type trống nhưng reference_type = morph key
                        $q2->where('reference_type', $seoType)
                            ->where(function ($q3) {
                                $q3->whereNull('type')->orWhere('type', '');
                            });
                    });
            })
            ->first();

        return $trans?->slug_full ? $this->normalizeSlugFull($trans->slug_full) : null;
    }

    /**
     * Breadcrumb từ chuỗi parent SEO (không hardcode prepend hub).
     *
     * @return array<int, array{label: string, url?: string|null}>
     */
    public function breadcrumbsForEntry(SeoEntry $entry, string $locale): array
    {
        $chain = [];
        $current = $entry->relationLoaded('parent') ? $entry : $entry->load(['parent.translations', 'translations']);
        $guard = 0;

        while ($current && $guard++ < 10) {
            $trans = $current->translation($locale) ?? $current->translation(Language::defaultCode() ?? 'vi');
            if ($trans) {
                array_unshift($chain, [
                    'label' => $trans->title ?: ($trans->seo_title ?: $trans->slug),
                    'url' => $this->publicUrl($trans, $locale),
                    'slug_full' => $trans->slug_full,
                ]);
            }
            $current = $current->parent;
            if ($current && ! $current->relationLoaded('translations')) {
                $current->load(['translations', 'parent']);
            }
        }

        $count = count($chain);
        $items = [];
        foreach ($chain as $i => $item) {
            $row = ['label' => $item['label']];
            if ($i < $count - 1 && ! empty($item['url'])) {
                $row['url'] = $item['url'];
            }
            $items[] = $row;
        }

        return $items;
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
     * @param  string|list<string>|null  $parentType
     * @return Collection<int, SeoEntry>
     */
    public function parentOptions(string|array|null $parentType, ?int $excludeId = null): Collection
    {
        $types = array_values(array_filter((array) $parentType));
        if ($types === []) {
            return collect();
        }

        $query = SeoEntry::query()
            ->with(['translations'])
            ->whereIn('type', $types);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('level')->orderBy('id')->get();
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

            $languageId = Language::idByCode($locale);
            $trans = $languageId
                ? ($existing->relationLoaded('translations')
                    ? $existing->translations->firstWhere('language_id', $languageId)
                    : $existing->translations()->where('language_id', $languageId)->first())
                : null;

            $parentIdProvided = array_key_exists('parent_id', $data);
            $desiredParentId = null;
            if ($parentIdProvided) {
                $raw = $data['parent_id'];
                $desiredParentId = ($raw !== null && $raw !== '' && (int) $raw > 0) ? (int) $raw : null;
            }
            $parentChanged = $parentIdProvided
                && (int) ($existing->parent_id ?? 0) !== (int) ($desiredParentId ?? 0);
            $missingSlugFull = ! $trans || ! filled($trans->slug_full);

            if ($missingSlugFull || $parentChanged) {
                return $this->syncSeo($model, $locale, array_merge([
                    'slug' => $trans?->slug ?? ($data['slug'] ?? Str::slug((string) ($data['title'] ?? 'page'))),
                    'title' => $trans?->title ?? ($data['title'] ?? null),
                    'seo_title' => $trans?->seo_title ?? ($data['seo_title'] ?? null),
                    'status' => $trans?->status ?? ($data['status'] ?? 'published'),
                ], $data), $seoType);
            }

            return $existing;
        }

        return $this->syncSeo($model, $locale, $data, $seoType);
    }

    /**
     * Hub cấp 1 từ config('seo.hubs.{key}') — StaticPage + SEO type tương ứng.
     */
    public function ensureHub(string $hubKey, string $locale = 'vi'): SeoEntry
    {
        $cfg = config("seo.hubs.{$hubKey}");
        if (! is_array($cfg)) {
            throw new \InvalidArgumentException("Unknown SEO hub: {$hubKey}");
        }

        $page = \App\Models\StaticPage::query()->firstOrCreate(
            ['template' => $cfg['template']],
            ['status' => 'published', 'published_at' => now()],
        );

        $languageId = Language::idByCode($locale);
        if ($languageId && ! $page->translations()->where('language_id', $languageId)->exists()) {
            $page->translations()->create([
                'language_id' => $languageId,
                'title' => $cfg['default_title'],
                'body' => $cfg['default_subtitle'] ?? null,
            ]);
        }

        $title = $page->translation($locale)?->title ?? $cfg['default_title'];

        return $this->ensureSeoFor($page, $cfg['seo_type'], $locale, [
            'slug' => $cfg['default_slug'],
            'title' => $title,
            'seo_title' => $cfg['default_seo_title'] ?? $title,
            'seo_description' => $cfg['default_seo_description'] ?? null,
            'status' => 'published',
            'parent_id' => null,
        ]);
    }

    public function ensureToursHub(string $locale = 'vi'): SeoEntry
    {
        return $this->ensureHub('tours_hub', $locale);
    }

    public function ensureCruisesHub(string $locale = 'vi'): SeoEntry
    {
        return $this->ensureHub('cruises_hub', $locale);
    }

    public function ensureGuideHub(string $locale = 'vi'): SeoEntry
    {
        return $this->ensureHub('guide_hub', $locale);
    }

    /**
     * Gắn mọi SEO type con vào hub (parent + level + rebuild slug_full).
     */
    public function attachChildrenToHub(string $childType, string $hubKey, string $locale = 'vi'): SeoEntry
    {
        $hub = $this->ensureHub($hubKey, $locale);
        $languageId = Language::idByCode($locale);

        SeoEntry::query()
            ->where('type', $childType)
            ->where(function ($q) use ($hub) {
                $q->whereNull('parent_id')->orWhere('parent_id', '!=', $hub->id);
            })
            ->with('translations')
            ->each(function (SeoEntry $entry) use ($hub, $locale, $childType, $languageId) {
                $entry->forceFill([
                    'parent_id' => $hub->id,
                    'level' => (int) $hub->level + 1,
                ])->save();

                $trans = $entry->translations->firstWhere('language_id', $languageId)
                    ?? $entry->translation($locale);
                if ($trans && filled($trans->slug) && (int) $trans->language_id === (int) $languageId) {
                    $newFull = $this->buildSlugFull($childType, $locale, (string) $trans->slug, $hub);
                    if ($trans->slug_full !== $newFull) {
                        $oldFull = $trans->slug_full;
                        $trans->forceFill(['slug_full' => $newFull])->save();
                        if ($languageId) {
                            $this->createRedirect301($oldFull, $newFull, $languageId);
                        }
                    }
                }

                $this->cascadeSlugFullChildren($entry->fresh(['translations', 'children.translations']), $locale);
            });

        return $hub;
    }

    public function attachCountriesToToursHub(string $locale = 'vi'): SeoEntry
    {
        return $this->attachChildrenToHub('country', 'tours_hub', $locale);
    }

    public function attachCruiseTypesToCruisesHub(string $locale = 'vi'): SeoEntry
    {
        return $this->attachChildrenToHub('cruise_type', 'cruises_hub', $locale);
    }

    public function attachBlogCategoriesToGuideHub(string $locale = 'vi'): SeoEntry
    {
        return $this->attachChildrenToHub('blog_category', 'guide_hub', $locale);
    }
}
