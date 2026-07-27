<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;
use Illuminate\Http\Request;

class TourController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function index(Request $request, string $country)
    {
        $countryData = $this->data->country($country) ?? abort(404);
        $tours = $this->filterTours($this->data->toursByCountry($country), $request);

        return view('pages.tours.index', [
            'country' => $countryData,
            'tours' => $tours,
            'styles' => $this->data->travelStyles(),
            'durations' => $this->data->durationBuckets(),
            'faqs' => $this->data->listingFaqs(),
        ]);
    }

    public function show(string $country, string $slug)
    {
        $tour = $this->data->tour($slug);
        if (! $tour || $tour['countrySlug'] !== $country) {
            abort(404);
        }

        $related = array_slice(
            array_values(array_filter(
                $this->data->toursByCountry($country),
                fn ($t) => $t['slug'] !== $slug
            )),
            0,
            3
        );

        return view('pages.tours.show', [
            'tour' => $tour,
            'related' => $related,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tours
     * @return array<int, array<string, mixed>>
     */
    protected function filterTours(array $tours, Request $request): array
    {
        $durations = (array) $request->input('duration', []);
        if ($request->filled('duration') && ! is_array($request->input('duration'))) {
            $durations = [$request->input('duration')];
        }
        $styles = (array) $request->input('style', []);
        $q = mb_strtolower(trim((string) $request->input('q', '')));

        if ($durations === [] && $styles === [] && $q === '') {
            return $tours;
        }

        return array_values(array_filter($tours, function (array $tour) use ($durations, $styles, $q) {
            if ($q !== '') {
                $haystack = mb_strtolower(implode(' ', [
                    $tour['title'] ?? '',
                    $tour['country'] ?? '',
                    $tour['start'] ?? '',
                    $tour['end'] ?? '',
                    implode(' ', $tour['places'] ?? []),
                    $tour['quote']['text'] ?? '',
                ]));
                if (! str_contains($haystack, $q)) {
                    return false;
                }
            }

            if ($durations !== []) {
                $days = (int) ($tour['days'] ?? 0);
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
                $tourStyles = $tour['styles'] ?? [];
                if (array_intersect($styles, $tourStyles) === []) {
                    return false;
                }
            }

            return true;
        }));
    }
}
