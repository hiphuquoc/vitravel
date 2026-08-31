<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\BlogCategory;
use App\Models\ContentTypeTag;
use App\Models\Country;
use App\Models\KeywordTag;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $query = Article::query()->with(['translations', 'blogCategory.translations', 'country.translations']);

        if ($request->filled('blog_category_id')) {
            $query->where('blog_category_id', $request->integer('blog_category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->whereHas('translations', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $articles = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $categories = BlogCategory::query()->with('translations')->orderBy('sort')->get();

        return view('admin.article.list', compact('articles', 'categories'));
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString();
        $article = $request->filled('id')
            ? Article::query()->with([
                'translations',
                'blogCategory.seoEntry.translations',
                'country',
                'contentTypeTags',
                'keywordTags',
                'relatedPackages.translations',
                'mediaAttachments.media',
                'seoEntry.translations',
                'seoEntry.parent',
            ])->findOrFail($request->integer('id'))
            : null;

        $languages = $this->activeLanguages();
        $translation = $article?->translation($locale);
        $seoTranslation = $article?->seoEntry?->translation($locale);
        $categories = BlogCategory::query()->with(['translations', 'seoEntry'])->orderBy('sort')->get();
        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $contentTypeTags = ContentTypeTag::query()->with('translations')->where('is_active', true)->get();
        $keywordTags = KeywordTag::query()->with('translations')->where('is_active', true)->get();
        $packages = Package::query()->with('translations')->orderByDesc('id')->limit(200)->get();
        $parents = $this->seoService()->parentOptionsForType('article');

        $defaultParentId = old(
            'seo_parent_id',
            $article?->seoEntry?->parent_id
                ?? $article?->blogCategory?->seoEntry?->id
        );

        $title = $article ? 'Chỉnh sửa bài viết' : 'Thêm bài viết mới';

        return view('admin.article.view', compact(
            'article', 'locale', 'languages', 'translation', 'seoTranslation',
            'categories', 'countries', 'contentTypeTags', 'keywordTags', 'packages',
            'title', 'parents', 'defaultParentId',
        ));
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        $locale = $request->string('language', 'vi')->toString();

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
            'seo_description' => 'nullable|string|max:350',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
            'content_type_tag_ids' => 'nullable|array',
            'content_type_tag_ids.*' => 'integer|exists:content_type_tags,id',
            'keyword_tag_ids' => 'nullable|array',
            'keyword_tag_ids.*' => 'integer|exists:keyword_tags,id',
            'related_package_ids' => 'nullable|array',
            'related_package_ids.*' => 'integer|exists:packages,id',
            ...$this->coverImageRules(),
        ]);

        $article = DB::transaction(function () use ($request, $validated, $locale) {
            $article = isset($validated['id'])
                ? Article::query()->findOrFail($validated['id'])
                : new Article;

            $article->fill([
                'country_id' => $validated['country_id'] ?? null,
                'blog_category_id' => $validated['blog_category_id'] ?? null,
                'author_name' => $validated['author_name'] ?? null,
                'status' => $validated['status'],
                'published_at' => $validated['status'] === 'published' ? now() : null,
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

            $this->syncCoverAttachment($article, $request, config('media.articles'));

            return $article;
        });

        return redirect()
            ->route('admin.articles.view', ['id' => $article->id, 'language' => $locale])
            ->with('success', 'Đã lưu bài viết thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $article = Article::query()->findOrFail($request->integer('id'));
        app(\App\Services\Purge\EntityPurgeService::class)->purge($article);

        return redirect()->route('admin.articles.list')->with('success', 'Đã xóa bài viết thành công.');
    }
}
