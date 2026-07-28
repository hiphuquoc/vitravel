<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;

class HomeController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index()
    {
        return view('pages.home', [
            'countries' => $this->data->homeCountries(),
            'featuredTours' => $this->data->featuredTours(),
            'featuredCruises' => $this->data->featuredCruises(),
            'pills' => $this->data->heroPills(),
            'slides' => $this->data->homeSlides(),
        ]);
    }
}
