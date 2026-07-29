<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Services\ViewDataService;

class PageController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function about()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.about', [
            'team' => $this->data->team(),
            'values' => $this->data->values(),
            'reasons' => $this->data->reasons(),
            'referencePersons' => $this->data->referencePersons(),
        ])->render());
    }

    public function contact()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.contact', [
            'offices' => $this->data->offices(),
        ])->render());
    }

    public function customize()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.customize-tour')->render());
    }

    public function team()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.team', [
            'team' => $this->data->team(),
        ])->render());
    }

    public function reviews()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.reviews', [
            'testimonials' => $this->data->testimonials(),
        ])->render());
    }

    public function gallery()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.gallery', [
            'albums' => $this->data->galleryAlbums(),
        ])->render());
    }

    public function videos()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.videos', [
            'videos' => $this->data->videos(),
        ])->render());
    }
}
