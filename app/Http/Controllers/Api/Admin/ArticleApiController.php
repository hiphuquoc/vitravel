<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\BlogCategory;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\KeywordTag;
use App\Models\Language;
use App\Models\Package;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleApiController extends Controller
{
    use ManagesTranslations;

    public function index(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        $query = Article::query()->with([
            'translations',
            'blogCategory.translations',
            'country.translations',
            'seoEntry.translations',
            'mediaAttachments.media',
        ]);

        if ($request->filled('blog_category_id')) {
            $query->where('blog_category_id', $request->integer('blog_category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $paginator = $query->orderByDesc('id')->paginate(
            min(max($request->integer('per_page', 20), 1), 100)
        );

        $items = collect($paginator->items())->map(fn (Article $a) => $this->serialize($a, $locale));

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function meta(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        $parents = $this->seoService()->parentOptionsForType('article');

        return ApiResponse::success([
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'statuses' => [
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'published', 'label' => 'Xuất bản'],
                ['value' => 'archived', 'label' => 'Lưu trữ'],
            ],
            'seo_parents' => $this->mapSeoParents($parents, $locale),
            'categories' => BlogCategory::query()->with('translations')->orderBy('sort')->get()->map(
                fn (BlogCategory $c) => [
                    'id' => $c->id,
                    'name' => $c->translation($locale)?->name,
                    'seo_id' => $c->seoEntry?->id,
                ]
            )->values(),
            'countries' => Country::query()->with('translations')->orderBy('sort')->get()->map(
                fn (Country $c) => [
                    'id' => $c->id,
                    'name' => $c->translation($locale)?->name ?? $c->code,
                    'code' => $c->code,
                ]
            )->values(),
            'content_type_tags' => ContentTypeTag::query()->with('translations')->where('is_active', true)->get()->map(
                fn (ContentTypeTag $t) => [
                    'id' => $t->id,
                    'name' => $t->translation($locale)?->name ?? $t->translation()?->name,
                ]
            )->values(),
            'keyword_tags' => KeywordTag::query()->with('translations')->where('is_active', true)->get()->map(
                fn (KeywordTag $t) => [
                    'id' => $t->id,
                    'name' => $t->translation($locale)?->name ?? $t->translation()?->name,
                ]
            )->values(),
            'packages' => Package::query()->with('translations')->orderByDesc('id')->limit(200)->get()->map(
                fn (Package $p) => [
                    'id' => $p->id,
                    'name' => $p->translation($locale)?->title ?? $p->code,
                ]
            )->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);
        $article = Article::query()->with([
            'translations',
            'blogCategory.seoEntry.translations',
            'country',
            'contentTypeTags',
            'keywordTags',
            'relatedPackages.translations',
            'mediaAttachments.media',
            'seoEntry.translations',
            'seoEntry.parent',
        ])->findOrFail($id);

        return ApiResponse::success($this->serializeDetail($article, $locale));
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->merge(['id' => $id]);

        return $this->save($request);
    }

    public function destroy(int $id): JsonResponse
    {
        Article::query()->findOrFail($id)->delete();

        return ApiResponse::success(null, 'Đã xóa bài viết');
    }

    private function save(Request $request): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();
        app()->setLocale($locale);

        try {
            $validated = $request->validate([
                'id' => 'nullable|integer|exists:articles,id',
                'country_id' => 'nullable|integer|exists:countries,id',
                'blog_category_id' => 'nullable|integer|exists:blog_categories,id',
                'author_name' => 'nullable|string|max:255',
                'status' => 'required|string|in:draft,published,archived',
                'title' => 'required|string|max:255',
                'excerpt' => 'nullable|string',
                'content' => 'nullable|string',
                'seo_slug' => 'nullable|string|max:191',
                'seo_title' => 'nullable|string|max:255',
                'seo_description' => 'nullable|string|max:320',
                'seo_keywords' => 'nullable|string|max:500',
                'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
                'content_type_tag_ids' => 'nullable|array',
                'content_type_tag_ids.*' => 'integer|exists:content_type_tags,id',
                'keyword_tag_ids' => 'nullable|array',
                'keyword_tag_ids.*' => 'integer|exists:keyword_tags,id',
                'related_package_ids' => 'nullable|array',
                'related_package_ids.*' => 'integer|exists:packages,id',
                'cover_media_id' => 'nullable|integer|exists:media,id',
                'remove_cover' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $article = DB::transaction(function () use ($request, $validated, $locale) {
            $article = isset($validated['id'])
                ? Article::query()->findOrFail($validated['id'])
                : new Article;

            $article->fill([
                'country_id' => $validated['country_id'] ?? null,
                'blog_category_id' => $validated['blog_category_id'] ?? null,
                'author_name' => $validated['author_name'] ?? null,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published'
                    ? ($article->published_at ?? now())
                    : null,
            ]);
            $article->save();

            $this->saveModelTranslation(
                $article,
                ArticleTranslation::class,
                'article_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'excerpt' => $validated['excerpt'] ?? null,
                    'content' => $validated['content'] ?? null,
                ],
                ['title', 'excerpt', 'content'],
            );

            $article->load(['country', 'blogCategory.translations', 'blogCategory.seoEntry']);
            $countryCode = $article->country?->code ?? 'vn';

            $categoryParentId = null;
            if ($article->blogCategory) {
                $cat = $article->blogCategory;
                $catSlug = $cat->translation($locale)?->slug
                    ?? $cat->translation()?->slug
                    ?? Str::slug((string) ($cat->translation($locale)?->name ?? 'category'));
                $catSeo = $this->seoService()->ensureSeoFor($cat, 'blog_category', $locale, [
                    'slug' => $catSlug,
                    'title' => $cat->translation($locale)?->name ?? $catSlug,
                    'seo_title' => $cat->translation($locale)?->name ?? $catSlug,
                    'status' => 'published',
                    'parent_id' => $this->seoService()->ensureGuideHub($locale)->id,
                ]);
                $categoryParentId = $catSeo->id;
            }

            $parentId = $validated['seo_parent_id'] ?? $categoryParentId;

            $this->saveSeoTranslations(
                $article,
                [
                    $locale => [
                        'slug' => $validated['seo_slug'] ?? $validated['title'],
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? null,
                        'seo_description' => $validated['seo_description'] ?? null,
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $validated['status'],
                        'country_code' => $countryCode,
                        'parent_id' => $parentId,
                    ],
                ],
                'article',
            );

            $article->contentTypeTags()->sync($validated['content_type_tag_ids'] ?? []);
            $article->keywordTags()->sync($validated['keyword_tag_ids'] ?? []);

            $related = [];
            foreach ($validated['related_package_ids'] ?? [] as $i => $packageId) {
                $related[$packageId] = ['sort' => $i];
            }
            $article->relatedPackages()->sync($related);

            app(MediaService::class)->syncCoverMediaId(
                $article,
                isset($validated['cover_media_id']) ? (int) $validated['cover_media_id'] : null,
                $request->boolean('remove_cover'),
            );

            return $article->fresh([
                'translations',
                'blogCategory.translations',
                'country',
                'contentTypeTags',
                'keywordTags',
                'relatedPackages.translations',
                'mediaAttachments.media',
                'seoEntry.translations',
                'seoEntry.parent',
            ]);
        });

        return ApiResponse::success(
            $this->serializeDetail($article, $locale),
            isset($validated['id']) ? 'Đã cập nhật bài viết' : 'Đã tạo bài viết',
            isset($validated['id']) ? 200 : 201,
        );
    }

    /** @return array<string, mixed> */
    private function serialize(Article $article, string $locale): array
    {
        $t = $article->translation($locale);
        $seo = $article->seoEntry?->translation($locale);
        $cover = $article->mediaAttachments->firstWhere('role', 'cover')?->media
            ?? $article->mediaAttachments->first()?->media;

        return [
            'id' => $article->id,
            'title' => $t?->title,
            'status' => $article->status,
            'author_name' => $article->author_name,
            'category' => $article->blogCategory ? [
                'id' => $article->blogCategory->id,
                'name' => $article->blogCategory->translation($locale)?->name,
            ] : null,
            'country' => $article->country ? [
                'id' => $article->country->id,
                'name' => $article->country->translation($locale)?->name,
            ] : null,
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
            ],
            'cover' => app(MediaService::class)->adminMediaPayload($cover, 'thumb'),
            'updated_at' => $article->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeDetail(Article $article, string $locale): array
    {
        $t = $article->translation($locale);
        $seo = $article->seoEntry?->translation($locale);
        $cover = $article->mediaAttachments->firstWhere('role', 'cover')?->media
            ?? $article->mediaAttachments->first()?->media;

        return array_merge($this->serialize($article, $locale), [
            'country_id' => $article->country_id,
            'blog_category_id' => $article->blog_category_id,
            'excerpt' => $t?->excerpt,
            'content' => $t?->content,
            'translated_locales' => $this->translatedLocaleCodes($article, 'title'),
            'content_type_tag_ids' => $article->contentTypeTags->pluck('id')->values()->all(),
            'keyword_tag_ids' => $article->keywordTags->pluck('id')->values()->all(),
            'related_package_ids' => $article->relatedPackages->pluck('id')->values()->all(),
            'cover' => app(MediaService::class)->adminMediaPayload($cover, 'card'),
            'seo' => [
                'slug' => $seo?->slug,
                'slug_full' => $seo?->slug_full,
                'title' => $seo?->seo_title,
                'description' => $seo?->seo_description,
                'keywords' => $seo?->keywords,
                'parent_id' => $article->seoEntry?->parent_id,
            ],
        ]);
    }
}
