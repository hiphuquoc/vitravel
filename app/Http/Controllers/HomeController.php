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
        // Featured grids tải AJAX lazy (không SSR mapPackage nặng)
        return $this->cachedHtmlResponse(fn () => view('pages.home', [
            'countries' => $this->data->homeCountries(),
            'pills' => $this->data->heroPills(),
            'slides' => $this->data->homeSlides(),
        ])->render(), [], true);
    }
}
