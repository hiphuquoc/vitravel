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
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\TourCategory;
use App\Support\ProjectContext;
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
        if ($parentEntry) {
            $this->assertParentHasLocale($parentEntry, $locale);
        }
        $slug = Str::slug((string) ($data['slug'] ?? ''));
        $context = $this->buildContext($model, $seoType, $data, $locale);

        // Cho phép seed/admin ghi đè slug_full (vd bài viết: hub/country/slug)
        if (filled($data['slug_full'] ?? null)) {
            $slugFull = $this->normalizeSlugFull((string) $data['slug_full']);
        } else {
            $slugFull = $this->buildSlugFull($seoType ?? '', $locale, $slug, $parentEntry, $context);
        }

        // withoutGlobalScope: orphan hub rows (project_id null) are invisible when context is set.
        $entry = $model->seoEntry()->withoutGlobalScope('project')->firstOrCreate([]);
        $this->ensureEntryProjectId($entry, $model);

        $existingTranslation = SeoEntryTranslation::withoutGlobalScope('project')
            ->where('seo_entry_id', $entry->id)
            ->where('language_id', $languageId)
            ->first();

        $this->assertSlugFullUnique(
            $languageId,
            $slugFull,
            $existingTranslation?->id,
            (bool) ($data['reclaim_slug_full'] ?? false),
        );

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
            'project_id' => $entry->project_id ?? ProjectContext::id(),
        ];

        // withoutGlobalScope: bản dịch cũ (project_id null / lệch) vẫn khớp unique (seo_entry_id, language_id).
        // Scoped updateOrCreate sẽ "không thấy" rồi INSERT → 1062 Duplicate entry.
        SeoEntryTranslation::withoutGlobalScope('project')->updateOrCreate(
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

        // URL cha phải cùng locale — tuyệt đối không fallback EN/VI (tránh zh-cn mượn /cruises).
        $parentSlugFull = $this->parentSlugFullForLocale($parentEntry, $locale);

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

    /**
     * slug_full của entry đúng locale (không fallback ngôn ngữ khác).
     * Tự vá khi slug đã đổi nhưng slug_full còn cũ (vd. leaf ≠ slug).
     */
    public function resolveEntrySlugFull(SeoEntry $entry, string $locale, bool $persistRepair = true): ?string
    {
        $trans = $entry->translationExact($locale);
        if (! $trans || ! filled($trans->slug)) {
            return null;
        }

        $slug = ltrim(Str::slug((string) $trans->slug), '/');
        if ($slug === '') {
            return null;
        }

        $currentFull = filled($trans->slug_full)
            ? $this->normalizeSlugFull((string) $trans->slug_full)
            : null;
        $leaf = $currentFull ? ltrim((string) basename($currentFull), '/') : null;
        $leafOk = $leaf !== null && $leaf !== '' && (
            $leaf === $slug || Str::slug($leaf) === $slug
        );

        if ($currentFull && $leafOk) {
            // Với node có cha: prefix cũng phải khớp cha cùng locale (không giữ /cruises mượn EN).
            if ($entry->parent_id) {
                $parent = $entry->relationLoaded('parent')
                    ? $entry->parent
                    : SeoEntry::query()->with('translations')->find($entry->parent_id);
                $parentFull = $this->parentSlugFullForLocale($parent, $locale, $persistRepair);
                $expected = filled($parentFull)
                    ? $this->normalizeSlugFull(rtrim((string) $parentFull, '/').'/'.$slug)
                    : $this->normalizeSlugFull('/'.$slug);
                if ($currentFull !== $expected) {
                    if ($persistRepair) {
                        $old = $trans->slug_full;
                        $trans->forceFill(['slug_full' => $expected])->save();
                        $languageId = Language::idByCode($locale);
                        if ($languageId) {
                            $this->createRedirect301($old, $expected, $languageId);
                        }
                    }

                    return $expected;
                }
            }

            return $currentFull;
        }

        $parent = null;
        if ($entry->parent_id) {
            $parent = $entry->relationLoaded('parent')
                ? $entry->parent
                : SeoEntry::query()->with('translations')->find($entry->parent_id);
        }

        $rebuilt = $this->buildSlugFull((string) ($entry->type ?? ''), $locale, $slug, $parent);
        if ($persistRepair && $rebuilt !== (string) $trans->slug_full) {
            $old = $trans->slug_full;
            $trans->forceFill(['slug_full' => $rebuilt])->save();
            $languageId = Language::idByCode($locale);
            if ($languageId) {
                $this->createRedirect301($old, $rebuilt, $languageId);
            }
        }

        return $rebuilt;
    }

    /**
     * Path cha cho locale đang build — không mượn EN/VI.
     */
    public function parentSlugFullForLocale(
        ?SeoEntry $parentEntry,
        string $locale,
        bool $persistRepair = true,
    ): ?string {
        if (! $parentEntry) {
            return null;
        }

        return $this->resolveEntrySlugFull($parentEntry, $locale, $persistRepair);
    }

    /**
     * Chặn lưu trang con khi trang cha chưa có bản dịch đúng locale
     * (tránh slug_full rơi về root /{slug}).
     *
     * @throws ValidationException
     */
    public function assertParentHasLocale(SeoEntry $parentEntry, string $locale): void
    {
        $parentEntry->loadMissing('translations.language');
        $exact = $parentEntry->translationExact($locale);
        if ($exact && filled($exact->slug)) {
            return;
        }

        $label = $parentEntry->translation()?->title
            ?: $parentEntry->translation()?->seo_title
            ?: '#'.$parentEntry->id;

        throw ValidationException::withMessages([
            'seo_parent_id' => [
                "Trang cha «{$label}» chưa có bản dịch / URL cho ngôn ngữ «{$locale}». ".
                'Hãy dịch trang cha trước khi lưu hoặc dịch trang con.',
            ],
        ]);
    }

    public function normalizeSlugFull(string $slugFull): string
    {
        $slugFull = '/'.trim(str_replace('//', '/', $slugFull), '/');

        return $slugFull === '/' ? '/' : $slugFull;
    }

    /**
     * @throws ValidationException
     */
    public function assertSlugFullUnique(
        int $languageId,
        string $slugFull,
        ?int $ignoreTranslationId = null,
        bool $reclaim = false,
    ): void {
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

        $conflicts = $query->get()->filter(fn (SeoEntryTranslation $trans) => $this->slugFullConflictIsActive($trans));
        if ($conflicts->isEmpty()) {
            return;
        }

        // Seed/rebuild: nhường đường dẫn — chỉ park slug_full, KHÔNG tạo 301
        // (createRedirect301 lúc reclaim dễ sinh vòng A↔B / self-redirect).
        if ($reclaim) {
            foreach ($conflicts as $conflict) {
                $this->parkTranslationSlugFull($conflict, '__orphaned');
            }

            return;
        }

        throw ValidationException::withMessages([
            'seo_slug' => 'Đường dẫn đầy đủ (slug_full) đã tồn tại cho ngôn ngữ này.',
        ]);
    }

    /**
     * Bỏ qua slug_full đã park hoặc thuộc bản ghi mồ côi.
     */
    protected function slugFullConflictIsActive(SeoEntryTranslation $trans): bool
    {
        $path = ltrim((string) $trans->slug_full, '/');
        if ($path === '' || str_starts_with($path, '__trashed-') || str_starts_with($path, '__orphaned-')) {
            return false;
        }

        $entry = SeoEntry::withoutGlobalScope('project')->find($trans->seo_entry_id);
        if (! $entry) {
            return false;
        }

        if (! $entry->reference_type || ! $entry->reference_id) {
            return false;
        }

        $ref = $entry->reference()->withoutGlobalScope('project')->first();

        return $ref !== null;
    }

    /**
     * Park slug_full khi reclaim seed/rebuild — giải phóng URL cho bản ghi mới.
     */
    public function parkTranslationSlugFull(SeoEntryTranslation $trans, string $prefix = '__orphaned'): void
    {
        $current = ltrim((string) $trans->slug_full, '/');
        if ($current === '' || str_starts_with($current, $prefix.'-')) {
            return;
        }

        $parked = $this->normalizeSlugFull('/'.$prefix.'-'.$trans->id.'/'.$current);
        $trans->forceFill([
            'slug_full' => $parked,
            'status' => 'draft',
        ])->save();
    }

    /**
     * Hitour SeoTranslation style: locale prefix nếu không phải default; upsert + chain-update.
     * Không tạo self-redirect / vòng 2 chiều (tránh ERR_TOO_MANY_REDIRECTS).
     */
    public function createRedirect301(?string $old, ?string $new, int $languageId): void
    {
        if (empty($old) || empty($new)) {
            return;
        }

        try {
            if (! Schema::hasTable('redirect_info')) {
                return;
            }

            $lang = Language::query()->find($languageId);
            $prefix = ($lang && ! $lang->is_default) ? '/'.$lang->code : '';

            $urlOld = $this->normalizeRedirectPath($prefix.'/'.ltrim($old, '/'));
            $urlNew = $this->normalizeRedirectPath($prefix.'/'.ltrim($new, '/'));

            if ($urlOld === '' || $urlNew === '' || $urlOld === $urlNew) {
                return;
            }

            // Không giữ chiều ngược (B→A) khi đang ghi A→B
            SeoRedirect::query()->where('url_old', $urlNew)->where('url_new', $urlOld)->delete();

            $exists = SeoRedirect::query()->where('url_old', $urlOld)->exists();
            if (! $exists) {
                SeoRedirect::query()->create([
                    'url_old' => $urlOld,
                    'url_new' => $urlNew,
                ]);
            } else {
                SeoRedirect::query()->where('url_old', $urlOld)->update(['url_new' => $urlNew]);
            }

            // Chain: mọi redirect đang trỏ vào old → trỏ tới new (trừ self)
            SeoRedirect::query()
                ->where('url_new', $urlOld)
                ->where('url_old', '!=', $urlNew)
                ->update(['url_new' => $urlNew]);

            // Dọn self-redirect do chain
            SeoRedirect::query()->whereColumn('url_old', 'url_new')->delete();
        } catch (\Throwable $e) {
            Log::warning('SeoService::createRedirect301 failed: '.$e->getMessage(), [
                'slug_old' => $old,
                'slug_new' => $new,
                'language_id' => $languageId,
            ]);
        }
    }

    protected function normalizeRedirectPath(string $path): string
    {
        $path = '/'.trim(str_replace('//', '/', $path), '/');

        return $path === '/' ? '' : $path;
    }

    /**
     * Xóa redirect hỏng: self-loop, vòng 2 chiều, đích rỗng.
     *
     * @return int số dòng đã xóa
     */
    public function purgeBadRedirects(): int
    {
        if (! Schema::hasTable('redirect_info')) {
            return 0;
        }

        $deleted = 0;

        $deleted += SeoRedirect::query()
            ->where(function ($q) {
                $q->whereNull('url_new')
                    ->orWhere('url_new', '')
                    ->orWhereNull('url_old')
                    ->orWhere('url_old', '');
            })
            ->delete();

        $deleted += SeoRedirect::query()->whereColumn('url_old', 'url_new')->delete();

        // Vòng 2 chiều A↔B
        $pairs = SeoRedirect::query()->get(['id', 'url_old', 'url_new']);
        $removeIds = [];
        $byOld = [];
        foreach ($pairs as $row) {
            $byOld[$row->url_old] = $row;
        }
        foreach ($pairs as $row) {
            $bounce = $byOld[$row->url_new] ?? null;
            if ($bounce && $bounce->url_new === $row->url_old) {
                $removeIds[] = $row->id;
                $removeIds[] = $bounce->id;
            }
        }
        $removeIds = array_values(array_unique($removeIds));
        if ($removeIds !== []) {
            $deleted += SeoRedirect::query()->whereIn('id', $removeIds)->delete();
        }

        return $deleted;
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

        // Truy vấn mọi con mà không bị chặn bởi project global scope
        $children = $parent->children()
            ->withoutGlobalScope('project')
            ->with(['translations' => fn ($q) => $q->withoutGlobalScope('project')])
            ->get();

        foreach ($children as $child) {
            // Cập nhật lại level của node con = level của cha + 1
            $newChildLevel = ((int) ($parent->level ?? 1)) + 1;
            if ((int) $child->level !== $newChildLevel) {
                $child->update(['level' => $newChildLevel]);
            }

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

            if ($this->normalizeSlugFull((string) ($childTrans->slug_full ?? '')) !== $this->normalizeSlugFull($newFull)) {
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
        $normalized = $this->normalizeSlugFull($path);
        $withoutSlash = ltrim($normalized, '/');
        if ($withoutSlash === '') {
            return null;
        }

        foreach (Language::contentLocaleChain($locale) as $code) {
            $languageId = Language::idByCode($code);
            if (! $languageId) {
                continue;
            }

            // 1. Thử tìm trong Scope dự án hiện tại trước
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

            // 2. Fallback tìm không phân biệt project_id (hỗ trợ các URL chuyên mục con của dự án con/tỉnh thành)
            if (! $trans) {
                $fallbackQuery = SeoEntryTranslation::withoutGlobalScope('project')
                    ->where('language_id', $languageId)
                    ->where(function ($q) use ($normalized, $withoutSlash) {
                        $q->where('slug_full', $normalized)
                            ->orWhere('slug_full', $withoutSlash);
                    });

                if ($publishedOnly) {
                    $fallbackQuery->where('status', 'published');
                }

                $trans = $fallbackQuery->first();
            }

            if (! $trans) {
                continue;
            }

            $transPath = ltrim((string) $trans->slug_full, '/');
            if ($transPath === ''
                || str_starts_with($transPath, '__trashed-')
                || str_starts_with($transPath, '__orphaned-')) {
                continue;
            }

            $entry = SeoEntry::withoutGlobalScope('project')
                ->with([
                    'reference' => fn ($q) => $q->withoutGlobalScope('project'),
                    'parent.translations',
                    'translations' => fn ($q) => $q->withoutGlobalScope('project'),
                ])
                ->find($trans->seo_entry_id);

            if (! $entry) {
                // Bản dịch mồ côi / thiếu entry — thử locale tiếp theo (thường là EN).
                continue;
            }

            $entry->setRelation('translation', $trans);

            return $entry;
        }

        return null;
    }

    public function hubPublicUrl(string $hubKey, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $entry = $this->ensureHub($hubKey, $locale);
        $trans = $entry->translationExact($locale) ?? $entry->translation($locale);

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
        $cacheKey = $routeName . ':' . $locale . ':' . json_encode($params);
        if (array_key_exists($cacheKey, $this->memoNamedPaths)) {
            return $this->memoNamedPaths[$cacheKey];
        }

        // Alias dự án 1 điểm đến (zones) → shape CMS (countries)
        if (in_array($routeName, ['guide.zone', 'guide.category'], true)) {
            $routeName = 'guide.country';
        }
        if (isset($params['zone']) && ! isset($params['country'])) {
            $params['country'] = $params['zone'];
        }
        if (isset($params['category']) && ! isset($params['country'])) {
            $params['country'] = $params['category'];
        }

        return $this->memoNamedPaths[$cacheKey] = match ($routeName) {
            'tours.hub' => $this->hubSlugFullPath('tours_hub', $locale),
            'cruises.hub' => $this->hubSlugFullPath('cruises_hub', $locale),
            'guide.index' => $this->hubSlugFullPath('guide_hub', $locale),
            'tours.index' => $this->countrySlugFullPath(
                is_array($params['country'] ?? null) ? null : ($params['country'] ?? null),
                $locale
            ),
            'tours.category' => $this->tourCategorySlugFullPath(
                is_array($params['country'] ?? null) ? null : ($params['country'] ?? null),
                $params['slug'] ?? ($params['category'] ?? null),
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
            'services.hub' => $this->serviceClusterHubPath($params['cluster'] ?? null, $locale),
            'services.index' => $this->serviceCategorySlugFullPath(
                $params['cluster'] ?? null,
                $params['category'] ?? null,
                $locale
            ),
            'services.show' => $this->serviceSlugFullPath($params['slug'] ?? null, $locale)
                ?? $this->composeSlugPath(
                    $this->serviceClusterHubPath($params['cluster'] ?? null, $locale),
                    $params['category'] ?? null,
                    $params['slug'] ?? null,
                ),
            default => null,
        };
    }

    protected function serviceClusterHubPath(?string $cluster, string $locale): ?string
    {
        if (! filled($cluster)) {
            return null;
        }
        $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
        if (! $hubKey) {
            return null;
        }

        return $this->hubSlugFullPath($hubKey, $locale);
    }

    protected function serviceCategorySlugFullPath(?string $cluster, ?string $categorySlug, string $locale): ?string
    {
        if (! filled($cluster) || ! filled($categorySlug)) {
            return null;
        }

        $cat = ServiceCategory::query()
            ->with(['seoEntry.translations'])
            ->where('cluster', $cluster)
            ->where('slug', $categorySlug)
            ->first();

        $full = $cat?->seoEntry?->translation($locale)?->slug_full;
        if ($full) {
            return $this->normalizeSlugFull($full);
        }

        $hub = $this->serviceClusterHubPath($cluster, $locale);
        if (! $hub) {
            return null;
        }

        return $this->normalizeSlugFull(rtrim($hub, '/').'/'.$categorySlug);
    }

    protected function serviceSlugFullPath(?string $slug, string $locale): ?string
    {
        if (! filled($slug)) {
            return null;
        }

        $full = $this->seoSlugFullByTypeAndSlug('service', $slug, $locale);

        return $full ? $this->normalizeSlugFull($full) : null;
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

    protected array $memoHubPaths = [];
    protected array $memoNamedPaths = [];
    protected array $memoHubEntries = [];

    public function hubSlugFullPath(string $hubKey, string $locale): string
    {
        $cacheKey = $hubKey . ':' . $locale;
        if (isset($this->memoHubPaths[$cacheKey])) {
            return $this->memoHubPaths[$cacheKey];
        }

        $entry = $this->ensureHub($hubKey, $locale);
        $full = $this->resolveEntrySlugFull($entry, $locale)
            ?? '/'.ltrim((string) (config("seo.hubs.{$hubKey}.default_slug") ?? $hubKey), '/');

        return $this->memoHubPaths[$cacheKey] = $this->normalizeSlugFull((string) $full);
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

    protected function tourCategorySlugFullPath(?string $countrySlug, ?string $categorySlug, string $locale): ?string
    {
        if (! filled($categorySlug)) {
            return null;
        }

        $countryPath = $this->countrySlugFullPath($countrySlug, $locale);
        $composed = $countryPath
            ? $this->normalizeSlugFull(rtrim($countryPath, '/').'/'.$categorySlug)
            : null;

        $full = $this->seoSlugFullByTypeAndSlug('tour_category', $categorySlug, $locale);
        if ($full) {
            $normalized = $this->normalizeSlugFull($full);
            // Orphan flat `/topic` (no parent in tree) → ưu tiên ghép theo country hub.
            $segments = array_values(array_filter(explode('/', trim($normalized, '/'))));
            if (count($segments) <= 1 && $composed) {
                return $composed;
            }

            return $normalized;
        }

        return $composed;
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

        $full = $type?->seoEntry
            ? $this->resolveEntrySlugFull($type->seoEntry, $locale)
            : null;
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
        $ids = Language::contentLanguageIdChain($locale);
        if ($ids === []) {
            return null;
        }

        $trans = SeoEntryTranslation::query()
            ->whereIn('language_id', $ids)
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
            ->get();

        // Ưu tiên theo chuỗi locale (current → en → vi)
        $picked = \App\Support\LocaleContent::firstTranslation($trans, $locale);

        return $picked?->slug_full ? $this->normalizeSlugFull($picked->slug_full) : null;
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
     * parent_type từ config/seo.php — single source cho admin select trang cha.
     *
     * @return list<string>
     */
    public function parentTypesFor(string $seoType): array
    {
        $configured = config("seo.types.{$seoType}.parent_type");
        if ($configured === null || $configured === '') {
            return [];
        }

        return array_values(array_filter(array_map('strval', (array) $configured)));
    }

    /** @return Collection<int, SeoEntry> */
    public function parentOptionsForType(string $seoType, ?int $excludeId = null, ?string $cluster = null, ?int $projectId = null): Collection
    {
        return $this->parentOptions($this->parentTypesFor($seoType), $excludeId, $cluster, $projectId);
    }

    /**
     * @param  string|list<string>|null  $parentType
     * @return Collection<int, SeoEntry>
     */
    public function parentOptions(string|array|null $parentType, ?int $excludeId = null, ?string $cluster = null, ?int $projectId = null): Collection
    {
        $types = array_values(array_filter((array) $parentType));
        if ($types === []) {
            return collect();
        }

        $activeProjectId = $projectId ?? \App\Support\ProjectContext::id();

        $query = SeoEntry::withoutGlobalScope('project')
            ->with(['translations' => fn ($q) => $q->withoutGlobalScope('project')])
            ->whereIn('type', $types);

        if ($activeProjectId) {
            $query->where(function ($q) use ($activeProjectId) {
                $q->where('project_id', $activeProjectId)
                    ->orWhereNull('project_id');
            });
        }

        if ($cluster) {
            // Nếu có lọc cluster (ví dụ stay, experience, extra...)
            // Chỉ lấy SeoEntry là Hub tương ứng hoặc ServiceCategory có cluster tương ứng
            $query->where(function ($q) use ($cluster) {
                $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
                if ($hubKey) {
                    $q->where('type', $hubKey);
                }
                $q->orWhere(function ($sub) use ($cluster) {
                    $sub->where('type', 'service_category')
                        ->whereHasMorph('reference', [\App\Models\ServiceCategory::class], function ($catQ) use ($cluster) {
                            $catQ->withoutGlobalScope('project')->where('cluster', $cluster);
                        });
                });
            });
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->orderBy('level')->orderBy('id')->get();
    }

    /**
     * Resolve project_id from the related model, then ProjectContext.
     */
    protected function resolveProjectIdFor(Model $model): ?int
    {
        $fromModel = $model->getAttribute('project_id');
        if ($fromModel !== null && $fromModel !== '') {
            return (int) $fromModel;
        }

        return ProjectContext::id();
    }

    /**
     * Morph firstOrCreate / legacy rows can miss BelongsToProject::creating — force project_id.
     */
    protected function ensureEntryProjectId(SeoEntry $entry, Model $model): void
    {
        $projectId = $this->resolveProjectIdFor($model);
        if (! $projectId) {
            return;
        }

        if ((int) $entry->project_id === $projectId) {
            return;
        }

        $entry->forceFill(['project_id' => $projectId])->save();
    }

    /**
     * Ensure parent SEO exists for a related model (e.g. Country of a Package).
     */
    public function ensureSeoFor(Model $model, string $seoType, string $locale, array $data = []): SeoEntry
    {
        // Orphan SEO (project_id null) is hidden by BelongsToProject when context is set.
        $existing = $model->seoEntry()->withoutGlobalScope('project')->first();

        if ($existing) {
            $this->ensureEntryProjectId($existing, $model);

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

            $slugMismatch = false;
            if ($trans && filled($trans->slug) && ($parentChanged || $parentIdProvided || filled($existing->parent_id))) {
                $parentForExpected = null;
                if ($parentIdProvided) {
                    $parentForExpected = $desiredParentId
                        ? SeoEntry::query()->with('translations')->find($desiredParentId)
                        : null;
                } elseif ($existing->parent_id) {
                    $parentForExpected = SeoEntry::query()->with('translations')->find($existing->parent_id);
                }
                // Ưu tiên slug đã lưu — không so với default_slug trong $data (ensureHub/seed),
                // kẻo custom slug hub bị coi là «lệch» rồi bị ghi đè về mặc định.
                $slugForExpected = filled($trans->slug)
                    ? (string) $trans->slug
                    : (string) ($data['slug'] ?? '');
                $expectedFull = $this->buildSlugFull(
                    $seoType,
                    $locale,
                    $slugForExpected,
                    $parentForExpected,
                );
                $slugMismatch = $this->normalizeSlugFull((string) $trans->slug_full) !== $expectedFull;
            }

            if ($missingSlugFull || $parentChanged || $slugMismatch) {
                // ensure* chỉ vá cấu trúc (parent / slug_full). Nội dung SEO đã có phải thắng
                // default trong $data — trước đây array_merge([preserved], $data) để $data ghi đè.
                $fallbackSlug = $data['slug'] ?? Str::slug((string) ($data['title'] ?? 'page'));
                $preserved = [
                    'slug' => filled($trans?->slug) ? (string) $trans->slug : $fallbackSlug,
                    'title' => filled($trans?->title) ? $trans->title : ($data['title'] ?? null),
                    'seo_title' => filled($trans?->seo_title) ? $trans->seo_title : ($data['seo_title'] ?? null),
                    'seo_description' => filled($trans?->seo_description)
                        ? $trans->seo_description
                        : ($data['seo_description'] ?? null),
                    'description' => filled($trans?->description)
                        ? $trans->description
                        : ($data['description'] ?? null),
                    'keywords' => filled($trans?->keywords) ? $trans->keywords : ($data['keywords'] ?? null),
                    'status' => filled($trans?->status) ? $trans->status : ($data['status'] ?? 'published'),
                    'canonical_url' => filled($trans?->canonical_url)
                        ? $trans->canonical_url
                        : ($data['canonical_url'] ?? null),
                ];

                return $this->syncSeo($model, $locale, array_merge($data, $preserved), $seoType);
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
        $cacheKey = $hubKey . ':' . $locale;
        if (isset($this->memoHubEntries[$cacheKey])) {
            return $this->memoHubEntries[$cacheKey];
        }
        $cfg = config("seo.hubs.{$hubKey}");
        if (! is_array($cfg)) {
            throw new \InvalidArgumentException("Unknown SEO hub: {$hubKey}");
        }

        $projectId = ProjectContext::id();
        $page = \App\Models\StaticPage::query()->firstOrCreate(
            ['template' => $cfg['template']],
            [
                'status' => 'published',
                'published_at' => now(),
                'project_id' => $projectId,
            ],
        );
        if (! $page->project_id && $projectId) {
            $page->forceFill(['project_id' => $projectId])->save();
        }

        $languageId = Language::idByCode($locale);
        if ($languageId && ! $page->translations()->where('language_id', $languageId)->exists()) {
            $page->translations()->create([
                'language_id' => $languageId,
                'title' => $cfg['default_title'],
                'body' => $cfg['default_subtitle'] ?? null,
                'seo_body' => \App\Support\ListingHubCopy::seoBody($hubKey, $locale) ?: null,
            ]);
        }

        $pageTitle = $page->translationExact($locale)?->title
            ?? $page->translation($locale)?->title
            ?? $cfg['default_title'];
        $page->loadMissing('seoEntry.translations');
        $seoTrans = $page->seoEntry?->translationExact($locale);

        // Không ép default_slug/seo_* khi bản dịch đúng locale đã tồn tại.
        // Không lấy slug EN/VI qua fallback — tránh tạo zh-cn với /cruises.
        return $this->ensureSeoFor($page, $cfg['seo_type'], $locale, [
            'slug' => filled($seoTrans?->slug) ? (string) $seoTrans->slug : $cfg['default_slug'],
            'title' => filled($seoTrans?->title) ? $seoTrans->title : $pageTitle,
            'seo_title' => filled($seoTrans?->seo_title)
                ? apply_site_brand((string) $seoTrans->seo_title)
                : apply_site_brand((string) ($cfg['default_seo_title'] ?? $pageTitle)),
            'seo_description' => filled($seoTrans?->seo_description)
                ? apply_site_brand((string) $seoTrans->seo_description)
                : apply_site_brand((string) ($cfg['default_seo_description'] ?? '')),
            'keywords' => $seoTrans?->keywords,
            'status' => filled($seoTrans?->status) ? $seoTrans->status : 'published',
            'parent_id' => null,
            // Seed/re-seed: nhường slug_full nếu bản ghi SEO cũ/orphan còn giữ đường dẫn hub.
            'reclaim_slug_full' => true,
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

    /**
     * Đồng bộ toàn bộ cây SEO public (idempotent).
     * Hub → country / cruise_type / blog_category → package / tour_category / article.
     * Chạy sau seed content hoặc khi thêm listing mới để tránh 404 do slug_full lệch cha/con.
     *
     * @param  list<string>|null  $locales  null = mọi ngôn ngữ active
     */
    public function rebuildPublicSeoTree(?array $locales = null): void
    {
        $codes = $locales ?? Language::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->pluck('code')
            ->all();

        foreach ($codes as $locale) {
            if (! Language::idByCode($locale)) {
                continue;
            }
            $this->rebuildToursSeoTree($locale);
            $this->rebuildCruisesSeoTree($locale);
            $this->rebuildGuideSeoTree($locale);
            $this->rebuildServicesSeoTree($locale);
        }

        $this->purgeBadRedirects();
    }

    /**
     * Rebuild SEO tree cho 5 cụm dịch vụ: hub → service_category → service.
     */
    public function rebuildServicesSeoTree(string $locale = 'vi'): void
    {
        foreach (config('services_catalog.clusters', []) as $cluster => $cfg) {
            $hubKey = $cfg['hub_key'] ?? null;
            if (! $hubKey) {
                continue;
            }

            $hub = $this->ensureHub($hubKey, $locale);

            ServiceCategory::query()
                ->forCluster($cluster)
                ->with(['seoEntry.translations'])
                ->each(function (ServiceCategory $cat) use ($hub, $locale) {
                    $this->ensureSeoFor($cat, 'service_category', $locale, [
                        'slug' => $cat->slug,
                        'title' => $cat->name,
                        'seo_title' => $cat->name,
                        'description' => $cat->intro,
                        'status' => 'published',
                        'parent_id' => $hub->id,
                        'reclaim_slug_full' => true,
                    ]);
                });

            Service::query()
                ->published()
                ->forCluster($cluster)
                ->with(['category.seoEntry', 'seoEntry.translations', 'translations'])
                ->each(function (Service $service) use ($hub, $locale) {
                    $parentId = $service->category?->seoEntry?->id ?: $hub->id;
                    $pkgTrans = $service->translation($locale) ?? $service->translation();
                    $seoTrans = $service->seoEntry?->translation($locale);
                    $title = $seoTrans?->title ?: ($pkgTrans?->title ?? null);
                    $slug = $seoTrans?->slug ?: (filled($title) ? Str::slug((string) $title) : null);
                    if (! filled($slug)) {
                        return;
                    }

                    $this->syncSeo($service, $locale, [
                        'slug' => $slug,
                        'title' => $title,
                        'seo_title' => $seoTrans?->seo_title ?: $title,
                        'description' => $seoTrans?->seo_description ?: ($pkgTrans?->summary ?? null),
                        'seo_description' => $seoTrans?->seo_description ?: ($pkgTrans?->summary ?? null),
                        'status' => $seoTrans?->status ?: 'published',
                        'parent_id' => $parentId,
                        'rating_aggregate_star' => $service->rating,
                        'rating_aggregate_count' => $service->review_count,
                        'reclaim_slug_full' => true,
                    ], 'service');
                });
        }
    }

    public function rebuildToursSeoTree(string $locale = 'vi'): void
    {
        $hub = $this->ensureToursHub($locale);

        Country::query()
            ->with(['translations', 'seoEntry.translations'])
            ->each(function (Country $country) use ($hub, $locale) {
                $trans = $country->translation($locale);
                if (! filled($trans?->slug)) {
                    return;
                }

                $this->ensureSeoFor($country, 'country', $locale, [
                    'slug' => $trans->slug,
                    'title' => $trans->name,
                    'seo_title' => $trans->name,
                    'description' => $trans->tagline,
                    'seo_description' => $trans->tagline,
                    'status' => 'published',
                    'parent_id' => $hub->id,
                    'country_code' => $country->code,
                    'reclaim_slug_full' => true,
                ]);
            });

        $this->attachCountriesToToursHub($locale);

        Package::query()
            ->tours()
            ->with(['country.seoEntry.translations', 'seoEntry.translations', 'translations'])
            ->each(function (Package $package) use ($locale, $hub) {
                $country = $package->country;
                $country?->load('seoEntry.translations');
                $existingParentId = $package->seoEntry?->parent_id;
                $allowedParentTypes = $this->parentTypesFor('package_tour');
                $keepExisting = false;
                if ($existingParentId) {
                    $existingParentType = SeoEntry::withoutGlobalScope('project')
                        ->whereKey($existingParentId)
                        ->value('type');
                    $keepExisting = $existingParentType
                        && in_array((string) $existingParentType, $allowedParentTypes, true);
                }
                $parentId = $keepExisting
                    ? $existingParentId
                    : ($country?->seoEntry?->id ?: $hub->id);
                if (! $parentId) {
                    return;
                }

                $pkgTrans = $package->translation($locale) ?? $package->translation();
                $seoTrans = $package->seoEntry?->translation($locale);
                $title = $seoTrans?->title ?: ($pkgTrans?->title ?? null);
                $slug = $seoTrans?->slug ?: (filled($title) ? Str::slug((string) $title) : null);
                if (! filled($slug)) {
                    return;
                }

                $this->syncSeo($package, $locale, [
                    'slug' => $slug,
                    'title' => $title,
                    'seo_title' => $seoTrans?->seo_title ?: $title,
                    'description' => $seoTrans?->seo_description,
                    'seo_description' => $seoTrans?->seo_description,
                    'status' => $seoTrans?->status ?: 'published',
                    'parent_id' => $parentId,
                    'rating_aggregate_star' => $package->rating,
                    'rating_aggregate_count' => $package->review_count,
                    'reclaim_slug_full' => true,
                ], 'package_tour');
            });

        if (class_exists(TourCategory::class)) {
            TourCategory::query()
                ->with(['country.seoEntry.translations', 'translations', 'seoEntry.translations'])
                ->each(function (TourCategory $category) use ($locale, $hub) {
                    $country = $category->country;
                    $existingParentId = $category->seoEntry()?->withoutGlobalScope('project')->value('parent_id')
                        ?? $category->seoEntry?->parent_id;
                    $allowedParentTypes = $this->parentTypesFor('tour_category');
                    $keepExisting = false;
                    if ($existingParentId) {
                        $existingParentType = SeoEntry::withoutGlobalScope('project')
                            ->whereKey($existingParentId)
                            ->value('type');
                        $keepExisting = $existingParentType
                            && in_array((string) $existingParentType, $allowedParentTypes, true);
                    }
                    $parentId = $keepExisting
                        ? $existingParentId
                        : ($country?->seoEntry?->id ?: $hub->id);
                    if (! $parentId) {
                        return;
                    }

                    $catTrans = $category->translation($locale) ?? $category->translation();
                    $seoEntry = $category->seoEntry()->withoutGlobalScope('project')->first();
                    $seoTrans = $seoEntry?->translation($locale);
                    $slug = $seoTrans?->slug ?: ($catTrans?->slug ?? null);
                    $title = $seoTrans?->title ?: ($catTrans?->name ?? null);
                    if (! filled($slug)) {
                        return;
                    }

                    $this->syncSeo($category, $locale, [
                        'slug' => $slug,
                        'title' => $title,
                        'seo_title' => $seoTrans?->seo_title ?: $title,
                        'description' => $seoTrans?->seo_description ?: ($catTrans?->description ?? null),
                        'seo_description' => $seoTrans?->seo_description ?: ($catTrans?->description ?? null),
                        'status' => $seoTrans?->status ?: 'published',
                        'parent_id' => $parentId,
                        'country_code' => $country?->code,
                        'reclaim_slug_full' => true,
                    ], 'tour_category');
                });
        }
    }

    public function rebuildCruisesSeoTree(string $locale = 'vi'): void
    {
        $hub = $this->ensureCruisesHub($locale);

        CruiseType::query()
            ->with(['seoEntry.translations'])
            ->each(function (CruiseType $type) use ($hub, $locale) {
                $this->ensureSeoFor($type, 'cruise_type', $locale, [
                    'slug' => $type->slug,
                    'title' => $type->name,
                    'seo_title' => $type->name,
                    'status' => 'published',
                    'parent_id' => $hub->id,
                    'reclaim_slug_full' => true,
                ]);
            });

        $this->attachCruiseTypesToCruisesHub($locale);

        Package::query()
            ->cruises()
            ->with(['cruiseType.seoEntry', 'seoEntry.translations', 'translations'])
            ->each(function (Package $package) use ($locale) {
                $cruiseType = $package->cruiseType;
                $cruiseType?->load('seoEntry.translations');
                $parentId = $cruiseType?->seoEntry?->id;
                if (! $parentId) {
                    return;
                }

                $pkgTrans = $package->translation($locale) ?? $package->translation();
                $seoTrans = $package->seoEntry?->translation($locale);
                $title = $seoTrans?->title ?: ($pkgTrans?->title ?? null);
                $slug = $seoTrans?->slug ?: (filled($title) ? Str::slug((string) $title) : null);
                if (! filled($slug)) {
                    return;
                }

                $this->syncSeo($package, $locale, [
                    'slug' => $slug,
                    'title' => $title,
                    'seo_title' => $seoTrans?->seo_title ?: $title,
                    'description' => $seoTrans?->seo_description,
                    'seo_description' => $seoTrans?->seo_description,
                    'status' => $seoTrans?->status ?: 'published',
                    'parent_id' => $parentId,
                    'rating_aggregate_star' => $package->rating,
                    'rating_aggregate_count' => $package->review_count,
                    'reclaim_slug_full' => true,
                ], 'package_cruise');
            });
    }

    public function rebuildGuideSeoTree(string $locale = 'vi'): void
    {
        $hub = $this->ensureGuideHub($locale);

        BlogCategory::query()
            ->with(['translations', 'seoEntry.translations'])
            ->each(function (BlogCategory $cat) use ($hub, $locale) {
                $trans = $cat->translation($locale) ?? $cat->translation();
                $slug = $trans?->slug ?: Str::slug((string) ($trans?->name ?? 'category-'.$cat->id));
                $title = $trans?->name ?? $slug;

                $this->ensureSeoFor($cat, 'blog_category', $locale, [
                    'slug' => $slug,
                    'title' => $title,
                    'seo_title' => $title,
                    'status' => 'published',
                    'parent_id' => $hub->id,
                    'reclaim_slug_full' => true,
                ]);
            });

        $this->attachBlogCategoriesToGuideHub($locale);

        Article::query()
            ->with(['blogCategory.seoEntry', 'seoEntry.translations', 'translations'])
            ->each(function (Article $article) use ($locale) {
                $category = $article->blogCategory;
                $category?->load('seoEntry.translations');
                $parentId = $category?->seoEntry?->id;
                if (! $parentId) {
                    return;
                }

                $artTrans = $article->translation($locale) ?? $article->translation();
                $seoTrans = $article->seoEntry?->translation($locale);
                $title = $seoTrans?->title ?: ($artTrans?->title ?? null);
                $slug = $seoTrans?->slug ?: (filled($title) ? Str::slug((string) $title) : null);
                if (! filled($slug)) {
                    return;
                }

                $this->syncSeo($article, $locale, [
                    'slug' => $slug,
                    'title' => $title,
                    'seo_title' => $seoTrans?->seo_title ?: $title,
                    'description' => $seoTrans?->seo_description ?: ($artTrans?->excerpt ?? null),
                    'seo_description' => $seoTrans?->seo_description ?: ($artTrans?->excerpt ?? null),
                    'status' => $seoTrans?->status ?: 'published',
                    'parent_id' => $parentId,
                    'rating_aggregate_star' => $article->rating,
                    'rating_aggregate_count' => $article->rating_count,
                    'reclaim_slug_full' => true,
                ], 'article');
            });
    }
}
