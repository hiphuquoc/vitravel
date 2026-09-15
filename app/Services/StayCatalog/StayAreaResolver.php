<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\StayArea;
use App\Support\StayCatalog\StayGeoParser;
use App\Support\StayCatalog\StayText;
use Illuminate\Support\Collection;

final class StayAreaResolver
{
    /**
     * @param  Collection<int, StayArea>|null  $areas
     */
    public function resolve(
        ?string $address = null,
        ?string $destId = null,
        ?float $lat = null,
        ?float $lng = null,
        ?string $bookingCc = null,
        ?Collection $areas = null,
    ): ?StayArea {
        $pool = $areas ?? StayArea::query()->where('is_active', true)->with('translations')->get();
        if ($pool->isEmpty()) {
            return null;
        }

        $destId = $destId !== null && $destId !== '' ? (string) $destId : null;
        if ($destId !== null) {
            $hit = $pool->first(fn (StayArea $a) => (string) $a->booking_dest_id === $destId);
            if ($hit) {
                return $hit;
            }
        }

        $geo = StayGeoParser::parseAddress($address);
        $haystack = StayText::fold(implode(' ', array_filter([
            $address,
            $geo['city'],
            $geo['district'],
            implode(' ', $geo['parts']),
        ])));

        $best = null;
        $bestScore = 0;
        foreach ($pool as $area) {
            if ($area->level === StayArea::LEVEL_COUNTRY) {
                continue;
            }
            $score = $this->aliasScore($area, $haystack);
            if ($lat !== null && $lng !== null) {
                $geoScore = $this->geoScore($area, $lat, $lng);
                $score = max($score, $geoScore);
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $area;
            }
        }

        if ($best !== null && $bestScore >= 40) {
            return $best;
        }

        if ($bookingCc !== null && strtolower($bookingCc) === 'vn') {
            $country = $pool->first(fn (StayArea $a) => $a->slug === 'vn');
            if ($country && $best === null) {
                return $country;
            }
        }

        return $bestScore >= 20 ? $best : null;
    }

    private function aliasScore(StayArea $area, string $haystack): int
    {
        if ($haystack === '') {
            return 0;
        }
        $needles = array_merge(
            [(string) $area->slug],
            is_array($area->aliases) ? $area->aliases : [],
        );
        if ($area->relationLoaded('translations')) {
            foreach ($area->translations as $tr) {
                $needles[] = (string) ($tr->name ?? '');
            }
        }
        $score = 0;
        foreach ($needles as $needle) {
            $fold = StayText::fold((string) $needle);
            if ($fold === '' || strlen($fold) < 4) {
                continue;
            }
            if (str_contains($haystack, $fold)) {
                $boost = match ($area->level) {
                    StayArea::LEVEL_ZONE => 90,
                    StayArea::LEVEL_DESTINATION => 70,
                    StayArea::LEVEL_REGION => 45,
                    default => 20,
                };
                $score = max($score, $boost);
            }
        }

        return $score;
    }

    private function geoScore(StayArea $area, float $lat, float $lng): int
    {
        $bbox = is_array($area->bbox) ? $area->bbox : null;
        if (is_array($bbox) && isset($bbox['min_lat'], $bbox['max_lat'], $bbox['min_lng'], $bbox['max_lng'])) {
            if ($lat >= (float) $bbox['min_lat'] && $lat <= (float) $bbox['max_lat']
                && $lng >= (float) $bbox['min_lng'] && $lng <= (float) $bbox['max_lng']) {
                return $area->level === StayArea::LEVEL_ZONE ? 85 : 65;
            }
        }
        if ($area->lat === null || $area->lng === null || ! $area->radius_meters) {
            return 0;
        }
        $meters = $this->haversine($lat, $lng, (float) $area->lat, (float) $area->lng);
        if ($meters <= (int) $area->radius_meters) {
            return $area->level === StayArea::LEVEL_ZONE ? 80 : 60;
        }

        return 0;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earth * asin(min(1, sqrt($a)));
    }
}
