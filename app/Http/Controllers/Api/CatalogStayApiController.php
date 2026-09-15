<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StayArea;
use App\Models\StayProperty;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CatalogStayApiController extends Controller
{
    public function areas(): JsonResponse
    {
        $items = StayArea::query()
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort')
            ->get()
            ->map(fn (StayArea $a) => [
                'id' => $a->id,
                'parent_id' => $a->parent_id,
                'slug' => $a->slug,
                'level' => $a->level,
                'name' => $a->displayName(),
                'country_code' => $a->country_code,
                'lat' => $a->lat,
                'lng' => $a->lng,
            ]);

        return ApiResponse::success(['items' => $items]);
    }

    public function index(Request $request): JsonResponse
    {
        $q = StayProperty::query()->where('status', 'published')->with(['translations', 'primaryArea']);
        if ($area = $request->input('area')) {
            if (is_numeric($area)) {
                $q->where('primary_stay_area_id', (int) $area);
            } else {
                $areaRow = StayArea::query()->where('slug', $area)->first();
                if ($areaRow) {
                    $ids = $areaRow->descendantIds(true);
                    $q->whereIn('primary_stay_area_id', $ids);
                }
            }
        }
        if ($type = trim((string) $request->input('property_type', ''))) {
            $q->where('property_type', $type);
        }
        if ($taxon = (int) $request->input('taxon_id')) {
            $q->whereHas('taxons', fn ($tq) => $tq->where('stay_taxons.id', $taxon));
        }
        if ($request->filled('bbox')) {
            $parts = array_map('floatval', explode(',', (string) $request->input('bbox')));
            if (count($parts) === 4) {
                [$minLng, $minLat, $maxLng, $maxLat] = $parts;
                $q->whereBetween('lat', [min($minLat, $maxLat), max($minLat, $maxLat)])
                    ->whereBetween('lng', [min($minLng, $maxLng), max($minLng, $maxLng)]);
            }
        }

        $perPage = min(max($request->integer('per_page', 20), 1), 50);
        if ($request->filled('after')) {
            $q->where('id', '<', (int) $request->input('after'));
            $rows = $q->orderByDesc('id')->limit($perPage + 1)->get();
            $hasMore = $rows->count() > $perPage;
            $items = $rows->take($perPage)->values();

            return ApiResponse::success([
                'items' => $items->map(fn (StayProperty $p) => $this->card($p)),
                'meta' => [
                    'per_page' => $perPage,
                    'next_cursor' => $hasMore ? $items->last()?->id : null,
                    'has_more' => $hasMore,
                ],
            ]);
        }

        $paginator = $q->orderByDesc('id')->paginate($perPage);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(fn (StayProperty $p) => $this->card($p)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $p = StayProperty::query()->with(['translations', 'primaryArea', 'taxons.translations'])->findOrFail($id);
        $attrs = is_array($p->attrs) ? $p->attrs : [];
        unset($attrs['crawl']);
        $photos = is_array($attrs['photos'] ?? null) ? $attrs['photos'] : [];
        $rooms = is_array($attrs['options'] ?? null) ? $attrs['options'] : [];
        $amenities = is_array($attrs['amenities'] ?? null) ? $attrs['amenities'] : [];

        return ApiResponse::success([
            'id' => $p->id,
            'source_hotel_key' => $p->source_hotel_key,
            'title' => $p->translation()?->title,
            'location_label' => $p->translation()?->location_label,
            'canonical_url' => $p->canonical_url,
            'property_type' => $p->property_type,
            'star_rating' => $p->star_rating,
            'rating' => $p->rating,
            'review_count' => $p->review_count,
            'lat' => $p->lat,
            'lng' => $p->lng,
            'price_from' => $p->price_from,
            'currency' => $p->currency,
            'area' => $p->primaryArea?->slug,
            'taxons' => $p->taxons->map(fn ($t) => [
                'id' => $t->id,
                'group' => $t->group,
                'code' => $t->code,
                'name' => $t->displayName(),
            ])->values(),
            'amenities' => $amenities,
            'rooms' => $rooms,
            'photos' => $photos,
            'attrs' => $attrs,
        ]);
    }

    /** @return array<string, mixed> */
    private function card(StayProperty $p): array
    {
        $attrs = is_array($p->attrs) ? $p->attrs : [];
        $photos = is_array($attrs['photos'] ?? null) ? $attrs['photos'] : [];
        $cover = $photos[0]['url'] ?? null;

        return [
            'id' => $p->id,
            'source_hotel_key' => $p->source_hotel_key,
            'title' => $p->translation()?->title,
            'property_type' => $p->property_type,
            'star_rating' => $p->star_rating,
            'rating' => $p->rating,
            'lat' => $p->lat,
            'lng' => $p->lng,
            'price_from' => $p->price_from,
            'currency' => $p->currency,
            'area' => $p->primaryArea?->slug,
            'cover' => $cover,
        ];
    }
}
