<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Services\ViewDataService;

class HomeController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    public function index()
    {
        return $this->cachedHtmlResponse(fn () => view('pages.home', [
            'countries' => $this->data->homeCountries(),
            'featuredTours' => $this->data->featuredTours(),
            'featuredCruises' => $this->data->featuredCruises(),
            'pills' => $this->data->heroPills(),
            'slides' => $this->data->homeSlides(),
        ])->render(), [], true);
    }
}
