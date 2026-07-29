<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Services\ViewDataService;

class TourController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    /**
     * Hub tất cả tour: /tours
     * Grid: client fetch; ItemList schema: SSR.
     */
    public function hub()
    {
        return $this->cachedHtmlResponse(function () {
            $hub = $this->data->toursHub();
            $tours = $this->data->tours();

            return view('pages.tours.hub', [
                'hub' => $hub,
                'tours' => $tours,
                'countries' => $this->data->countries(),
                'styles' => $this->data->travelStyles(),
                'durations' => $this->data->durationBuckets(),
                'faqs' => $this->data->listingFaqs(),
            ])->render();
        });
    }

    public function index(string $country)
    {
        return $this->cachedHtmlResponse(function () use ($country) {
            $countryData = $this->data->country($country) ?? abort(404);
            $tours = $this->data->toursByCountry($country);

            return view('pages.tours.index', [
                'country' => $countryData,
                'countries' => $this->data->countries(),
                'tours' => $tours,
                'styles' => $this->data->travelStyles(),
                'durations' => $this->data->durationBuckets(),
                'faqs' => $this->data->listingFaqs(),
            ])->render();
        });
    }

    public function show(string $country, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($country, $slug) {
            $tour = $this->data->tour($slug);
            if (! $tour || $tour['countrySlug'] !== $country) {
                abort(404);
            }

            return view('pages.tours.show', [
                'tour' => $tour,
            ])->render();
        });
    }
}
