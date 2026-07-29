<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Services\ViewDataService;

class GuideController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function index()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.guide.index', [
            'country' => null,
            'category' => null,
            'articles' => $this->data->articles(),
            'categories' => $this->data->blogCategories(),
            'contentTags' => $this->data->contentTags(),
            'keywords' => $this->data->popularKeywords(),
            'faqs' => $this->data->listingFaqs(),
        ])->render());
    }

    public function country(string $country)
    {
        return $this->cachedHtmlResponse(function () use ($country) {
            $category = $this->data->blogCategoryBySlug($country);
            if ($category) {
                return view('pages.guide.index', [
                    'country' => null,
                    'category' => $category,
                    'articles' => $this->data->articlesByCategorySlug($country),
                    'categories' => $this->data->blogCategories(),
                    'contentTags' => $this->data->contentTags(),
                    'keywords' => $this->data->popularKeywords(),
                    'faqs' => $this->data->listingFaqs(),
                ])->render();
            }

            $countryData = $this->data->country($country) ?? abort(404);

            return view('pages.guide.index', [
                'country' => $countryData,
                'category' => null,
                'articles' => $this->data->articlesByCountry($country),
                'categories' => $this->data->blogCategories(),
                'contentTags' => $this->data->contentTags(),
                'keywords' => $this->data->popularKeywords(),
                'faqs' => $this->data->listingFaqs(),
            ])->render();
        });
    }

    public function show(string $country, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($country, $slug) {
            $article = $this->data->article($slug) ?? abort(404);

            $relatedSource = $article['categorySlug'] ?? $article['countrySlug'] ?? $country;
            $relatedPool = $this->data->articlesByCategorySlug($relatedSource);
            if ($relatedPool === []) {
                $relatedPool = $this->data->articlesByCountry($relatedSource);
            }
            if ($relatedPool === []) {
                $relatedPool = $this->data->articles();
            }
            $related = array_slice(
                array_values(array_filter(
                    $relatedPool,
                    fn ($a) => ($a['slug'] ?? '') !== $slug
                )),
                0,
                2
            );

            return view('pages.guide.show', [
                'article' => $article,
                'related' => $related,
                'categories' => $this->data->blogCategories(),
                'keywords' => $this->data->popularKeywords(),
            ])->render();
        });
    }
}
