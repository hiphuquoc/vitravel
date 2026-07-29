<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersWithHtmlCache;
use App\Services\ViewDataService;

class CruiseController extends Controller
{
    use RendersWithHtmlCache;

    public function __construct(protected ViewDataService $data) {}

    /**
     * Hub tất cả du thuyền: /cruises
     */
    public function hub()
    {
        return $this->cachedHtmlResponse(function () {
            $hub = $this->data->cruisesHub();
            $cruises = $this->data->cruises();
            $types = $this->data->cruiseTypes();

            return view('pages.cruises.hub', [
                'hub' => $hub,
                'cruises' => $cruises,
                'types' => $types,
                'styles' => $this->data->travelStyles(),
                'durations' => $this->data->durationBuckets(),
                'faqs' => $this->data->listingFaqs(),
            ])->render();
        });
    }

    public function index(string $type)
    {
        return $this->cachedHtmlResponse(function () use ($type) {
            $types = collect($this->data->cruiseTypes());
            $typeData = $types->firstWhere('slug', $type) ?? abort(404);

            $cruises = array_values(array_filter(
                $this->data->cruises(),
                fn ($c) => $c['typeSlug'] === $type
            ));

            return view('pages.cruises.index', [
                'type' => $typeData,
                'types' => $types->all(),
                'cruises' => $cruises,
                'styles' => $this->data->travelStyles(),
                'durations' => $this->data->durationBuckets(),
                'faqs' => $this->data->listingFaqs(),
            ])->render();
        });
    }

    public function show(string $type, string $slug)
    {
        return $this->cachedHtmlResponse(function () use ($type, $slug) {
            $cruise = $this->data->cruise($slug);
            if (! $cruise || $cruise['typeSlug'] !== $type) {
                abort(404);
            }

            return view('pages.cruises.show', [
                'cruise' => $cruise,
            ])->render();
        });
    }
}
