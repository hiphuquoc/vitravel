<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;
use Illuminate\Http\Request;

class CruiseController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index(Request $request, string $type)
    {
        $types = collect($this->data->cruiseTypes());
        $typeData = $types->firstWhere('slug', $type) ?? abort(404);

        $cruises = array_values(array_filter(
            $this->data->cruises(),
            fn ($c) => $c['typeSlug'] === $type
        ));

        $cruises = $this->filterCruises($cruises, $request);

        return view('pages.cruises.index', [
            'type' => $typeData,
            'types' => $types->all(),
            'cruises' => $cruises,
            'styles' => $this->data->travelStyles(),
            'durations' => $this->data->durationBuckets(),
            'faqs' => $this->data->listingFaqs(),
        ]);
    }

    public function show(string $type, string $slug)
    {
        $cruise = $this->data->cruise($slug);
        if (! $cruise || $cruise['typeSlug'] !== $type) {
            abort(404);
        }

        $related = array_slice(
            array_values(array_filter(
                $this->data->cruises(),
                fn ($c) => $c['slug'] !== $slug && $c['typeSlug'] === $type
            )),
            0,
            3
        );

        return view('pages.cruises.show', [
            'cruise' => $cruise,
            'related' => $related,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $cruises
     * @return array<int, array<string, mixed>>
     */
    protected function filterCruises(array $cruises, Request $request): array
    {
        $durations = (array) $request->input('duration', []);
        if ($request->filled('duration') && ! is_array($request->input('duration'))) {
            $durations = [$request->input('duration')];
        }
        $styles = (array) $request->input('style', []);

        if ($durations === [] && $styles === []) {
            return $cruises;
        }

        return array_values(array_filter($cruises, function (array $cruise) use ($durations, $styles) {
            if ($durations !== []) {
                $days = (int) ($cruise['days'] ?? 0);
                $matchesDuration = collect($durations)->contains(function (string $bucket) use ($days) {
                    return match ($bucket) {
                        'lt7' => $days < 7,
                        '7-10' => $days >= 7 && $days <= 10,
                        '11-15' => $days >= 11 && $days <= 15,
                        'gt16' => $days > 15,
                        default => true,
                    };
                });
                if (! $matchesDuration) {
                    return false;
                }
            }

            if ($styles !== []) {
                if (array_intersect($styles, $cruise['styles'] ?? []) === []) {
                    return false;
                }
            }

            return true;
        }));
    }
}
