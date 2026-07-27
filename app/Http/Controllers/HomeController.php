<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;

class HomeController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index()
    {
        return view('pages.home', [
            'countries' => $this->data->countries(),
            'featuredTours' => $this->data->featuredTours(),
            'pills' => $this->data->heroPills(),
            'slides' => $this->data->homeSlides(),
        ]);
    }
}
