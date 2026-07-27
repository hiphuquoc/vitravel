<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;

class GuideController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index()
    {
        return view('pages.guide.index', [
            'country' => null,
            'articles' => $this->data->articles(),
            'categories' => $this->data->blogCategories(),
            'contentTags' => $this->data->contentTags(),
            'keywords' => $this->data->popularKeywords(),
            'faqs' => $this->data->listingFaqs(),
        ]);
    }

    public function country(string $country)
    {
        $countryData = $this->data->country($country) ?? abort(404);

        return view('pages.guide.index', [
            'country' => $countryData,
            'articles' => $this->data->articlesByCountry($country),
            'categories' => $this->data->blogCategories(),
            'contentTags' => $this->data->contentTags(),
            'keywords' => $this->data->popularKeywords(),
            'faqs' => $this->data->listingFaqs(),
        ]);
    }

    public function show(string $country, string $slug)
    {
        $article = $this->data->article($slug);
        if (! $article || $article['countrySlug'] !== $country) {
            abort(404);
        }

        $related = array_slice(
            array_values(array_filter(
                $this->data->articlesByCountry($country),
                fn ($a) => $a['slug'] !== $slug
            )),
            0,
            2
        );

        return view('pages.guide.show', [
            'article' => $article,
            'related' => $related,
            'categories' => $this->data->blogCategories(),
            'keywords' => $this->data->popularKeywords(),
        ]);
    }
}
