<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Language;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Models\StayCategoryBinding;
use App\Models\StayProperty;
use App\Models\StayTaxon;
use App\Services\SeoService;
use App\Support\ProjectContext;
use App\Support\StayCatalog\StayText;
use Illuminate\Support\Str;

final class StayCatalogProjectionService
{
    public function __construct(private readonly SeoService $seo) {}

    /**
     * @return array{created: int, attached: int, skipped: int}
     */
    public function syncBinding(StayCategoryBinding $binding): array
    {
        $binding->loadMissing(['area.children', 'taxon', 'extraAreas', 'category']);
        $category = $binding->category;
        if (! $category instanceof ServiceCategory) {
            return ['created' => 0, 'attached' => 0, 'skipped' => 0];
        }

        $areaIds = $this->areaIdsFor($binding);
        $query = StayProperty::query()->where('status', 'published');
        if ($areaIds !== []) {
            $query->where(function ($q) use ($areaIds): void {
                $q->whereIn('primary_stay_area_id', $areaIds)
                    ->orWhereHas('areas', fn ($aq) => $aq->whereIn('stay_areas.id', $areaIds));
            });
        }
        if ($binding->stay_taxon_id) {
            $taxon = $binding->taxon;
            $query->where(function ($q) use ($binding, $taxon): void {
                $q->whereHas('taxons', fn ($tq) => $tq->where('stay_taxons.id', $binding->stay_taxon_id));
                if ($taxon instanceof StayTaxon && str_starts_with((string) $taxon->code, 'ht_id=')) {
                    $type = $this->typeFromHtId($taxon);
                    if ($type) {
                        $q->orWhere('property_type', $type);
                    }
                }
            });
        }

        $created = 0;
        $attached = 0;
        $skipped = 0;
        $projectId = (int) ($category->project_id ?: ProjectContext::id());

        foreach ($query->with('translations')->cursor() as $property) {
            $service = $this->ensureProjection($property, $category, $projectId);
            if ($service->wasRecentlyCreated) {
                $created++;
            } else {
                $attached++;
            }
            $existing = $service->categories()->withoutGlobalScope('project')->pluck('service_categories.id')->all();
            if (! in_array((int) $category->id, $existing, true)) {
                $service->categories()->syncWithoutDetaching([$category->id]);
            }
        }

        $binding->last_synced_at = now();
        $binding->save();

        return ['created' => $created, 'attached' => $attached, 'skipped' => $skipped];
    }

    public function ensureProjection(StayProperty $property, ServiceCategory $category, ?int $projectId = null): Service
    {
        $projectId = $projectId ?: (int) ($category->project_id ?: ProjectContext::id());
        $existing = Service::withoutGlobalScopes()
            ->where('cluster', Service::CLUSTER_STAY)
            ->where('stay_property_id', $property->id)
            ->where('project_id', $projectId)
            ->first();
        if ($existing) {
            return $existing;
        }

        $title = (string) ($property->translation()?->title ?: $property->source_hotel_key);
        $slug = Str::slug($title) ?: str_replace(':', '-', $property->source_hotel_key);
        $service = new Service;
        $service->project_id = $projectId;
        $service->cluster = Service::CLUSTER_STAY;
        $service->service_category_id = $category->id;
        $service->stay_property_id = $property->id;
        $service->stay_area_id = $property->primary_stay_area_id;
        $service->code = 'bk-'.Str::limit(str_replace(':', '-', $property->source_hotel_key), 50, '');
        $service->price_from = $property->price_from;
        $service->currency = $property->currency ?: 'VND';
        $service->rating = $property->rating ?: 0;
        $service->review_count = $property->review_count ?: 0;
        $service->star_rating = $property->star_rating;
        $service->lat = $property->lat;
        $service->lng = $property->lng;
        $service->status = 'published';
        $service->published_at = now();
        $service->attrs = [
            'property_type' => $property->property_type,
            'crawl' => [
                'source' => $property->source,
                'source_hotel_key' => $property->source_hotel_key,
                'canonical_url' => $property->canonical_url,
                'projection' => true,
            ],
        ];
        $service->save();

        $langId = Language::idByCode('vi');
        if ($langId) {
            ServiceTranslation::query()->updateOrCreate(
                ['service_id' => $service->id, 'language_id' => $langId],
                [
                    'title' => $title,
                    'location_label' => $property->translation()?->location_label,
                    'content' => null,
                ],
            );
        }

        $this->seo->syncSeo($service, 'vi', [
            'slug' => $slug,
            'title' => $title,
            'description' => $title,
            'seo_title' => $title,
            'seo_description' => $title,
            'status' => 'published',
            'parent_id' => $category->seoEntry?->id,
            'reclaim_slug_full' => true,
        ], 'service');

        return $service;
    }

    /** @return list<int> */
    public function areaIdsFor(StayCategoryBinding $binding): array
    {
        $ids = [];
        if ($binding->area) {
            $binding->area->loadMissing(['children', 'relatedAreas']);
            $ids = $binding->include_child_areas
                ? $binding->area->descendantIds(true)
                : [(int) $binding->area->id];
            if ($binding->include_related_areas) {
                foreach ($binding->area->relatedAreas as $rel) {
                    $ids[] = (int) $rel->id;
                    if ($binding->include_child_areas) {
                        $ids = array_merge($ids, $rel->descendantIds(true));
                    }
                }
            }
        }
        foreach ($binding->extraAreas as $extra) {
            $ids[] = (int) $extra->id;
            if ($binding->include_child_areas) {
                $ids = array_merge($ids, $extra->descendantIds(true));
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function typeFromHtId(StayTaxon $taxon): ?string
    {
        $name = StayText::fold($taxon->displayName());

        return match (true) {
            str_contains($name, 'resort') => 'resort',
            str_contains($name, 'villa') || str_contains($name, 'bietthu') => 'villa',
            str_contains($name, 'homestay') => 'homestay',
            str_contains($name, 'apartment') || str_contains($name, 'canho') => 'apartment',
            str_contains($name, 'hostel') => 'hostel',
            str_contains($name, 'hotel') || str_contains($name, 'khachsan') => 'hotel',
            default => null,
        };
    }
}
