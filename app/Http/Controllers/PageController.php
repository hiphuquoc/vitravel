<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;

class PageController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function about()
    {
        return view('pages.about', [
            'team' => $this->data->team(),
            'values' => $this->data->values(),
            'reasons' => $this->data->reasons(),
            'referencePersons' => $this->data->referencePersons(),
        ]);
    }

    public function contact()
    {
        return view('pages.contact', [
            'offices' => $this->data->offices(),
        ]);
    }

    public function customize()
    {
        return view('pages.customize-tour');
    }

    public function team()
    {
        return view('pages.team', [
            'team' => $this->data->team(),
        ]);
    }

    public function reviews()
    {
        return view('pages.reviews', [
            'testimonials' => $this->data->testimonials(),
        ]);
    }

    public function gallery()
    {
        return view('pages.gallery', [
            'albums' => $this->data->galleryAlbums(),
        ]);
    }

    public function videos()
    {
        return view('pages.videos', [
            'videos' => $this->data->videos(),
        ]);
    }
}
