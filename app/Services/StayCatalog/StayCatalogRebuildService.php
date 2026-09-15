<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Language;
use App\Models\Service;
use App\Models\StayAmenity;
use App\Models\StayAmenityAlias;
use App\Models\StayAmenityTranslation;
use App\Models\StayCrawlItem;
use App\Models\StayPlace;
use App\Models\StayPlaceAlias;
use App\Models\StayPlaceTranslation;
use App\Models\StayProperty;
use App\Support\StayCatalog\StayCompleteness;
use App\Support\StayCatalog\StayGeoParser;
use App\Support\StayCatalog\StayIdentity;
use App\Support\StayCatalog\StayText;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class StayCatalogRebuildService
{
    public function __construct(
        private readonly StayAreaResolver $areas,
        private readonly StayPropertySyncService $properties,
        private readonly StayCatalogAreaSeed $areaSeed,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function stats(): array
    {
        $stay = Service::withoutGlobalScopes()->where('cluster', Service::CLUSTER_STAY);
        $total = (clone $stay)->count();
        $withKey = 0;
        $withGeo = 0;
        $withArea = 0;
        $scoreSum = 0;
        $unmatched = 0;
        foreach ((clone $stay)->select('id', 'lat', 'lng', 'stay_area_id', 'stay_property_id', 'attrs')->cursor() as $svc) {
            $attrs = is_array($svc->attrs) ? $svc->attrs : [];
            $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
            if (filled($crawl['source_hotel_key'] ?? null) || $svc->stay_property_id) {
                $withKey++;
            }
            if (is_numeric($svc->lat) && is_numeric($svc->lng)) {
                $withGeo++;
            } elseif (is_numeric($attrs['lat'] ?? null) && is_numeric($attrs['lng'] ?? null)) {
                $withGeo++;
            }
            if ($svc->stay_area_id) {
                $withArea++;
            } else {
                $unmatched++;
            }
            $scoreSum += StayCompleteness::fromAttrs($attrs, $crawl['source_hotel_key'] ?? null)['score'];
        }

        return [
            'stays' => $total,
            'with_identity' => $withKey,
            'with_geo' => $withGeo,
            'with_area' => $withArea,
            'unmatched_area' => $unmatched,
            'avg_completeness' => $total > 0 ? (int) round($scoreSum / $total) : 0,
            'properties' => Schema::hasTable('stay_properties') ? StayProperty::query()->count() : 0,
            'items' => StayCrawlItem::withoutGlobalScopes()->count(),
            'catalog_enabled' => (bool) config('stay.catalog.enabled', false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $layer, bool $dryRun = false, int $limit = 0, bool $seedAreas = true): array
    {
        return match ($layer) {
            'offline', 'r1' => $this->offline($dryRun, $limit),
            'areas', 'r2' => $this->assignAreas($dryRun, $limit, $seedAreas),
            'properties', 'r7' => $this->materializeProperties($dryRun, $limit),
            'improve', 'r4' => $this->queueImprove($dryRun, $limit),
            default => throw new \InvalidArgumentException('Layer không hợp lệ: '.$layer),
        };
    }

    /**
     * R1: identity + geo attrs + completeness + alias merge.
     *
     * @return array<string, mixed>
     */
    public function offline(bool $dryRun, int $limit = 0): array
    {
        $report = [
            'layer' => 'offline',
            'dry_run' => $dryRun,
            'items_keyed' => 0,
            'services_keyed' => 0,
            'geo_filled' => 0,
            'amenity_aliases' => 0,
            'place_aliases' => 0,
            'amenity_merged' => 0,
            'place_merged' => 0,
            'rows' => [],
        ];

        $itemQ = StayCrawlItem::withoutGlobalScopes()->orderBy('id');
        if ($limit > 0) {
            $itemQ->limit($limit);
        }
        foreach ($itemQ->cursor() as $item) {
            $identity = StayIdentity::fromUrl((string) ($item->canonical_url ?: $item->source_url));
            if (! $identity['source_hotel_key']) {
                continue;
            }
            $report['items_keyed']++;
            if (! $dryRun) {
                $item->source = $identity['source'];
                $item->source_hotel_key = $identity['source_hotel_key'];
                $item->booking_cc = $identity['booking_cc'];
                $item->saveQuietly();
            }
        }

        $svcQ = Service::withoutGlobalScopes()->where('cluster', Service::CLUSTER_STAY)->orderBy('id');
        if ($limit > 0) {
            $svcQ->limit($limit);
        }
        foreach ($svcQ->cursor() as $service) {
            $attrs = is_array($service->attrs) ? $service->attrs : [];
            $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
            $url = (string) ($crawl['canonical_url'] ?? $crawl['source_url'] ?? '');
            if ($url === '' && is_string($service->code) && str_starts_with($service->code, 'bk-')) {
                $url = 'https://www.booking.com/hotel/vn/'.substr($service->code, 3).'.html';
            }
            $identity = $url !== '' ? StayIdentity::fromUrl($url) : ['source_hotel_key' => $crawl['source_hotel_key'] ?? null, 'booking_cc' => null, 'canonical_url' => $url, 'source' => StayIdentity::SOURCE_BOOKING];
            if ($identity['source_hotel_key']) {
                $report['services_keyed']++;
                $crawl['source'] = $identity['source'] ?? StayIdentity::SOURCE_BOOKING;
                $crawl['source_hotel_key'] = $identity['source_hotel_key'];
                $crawl['booking_cc'] = $identity['booking_cc'] ?? ($crawl['booking_cc'] ?? null);
                $crawl['canonical_url'] = $identity['canonical_url'] ?: ($crawl['canonical_url'] ?? $url);
            }
            $geo = StayGeoParser::parseAddress(isset($attrs['address']) ? (string) $attrs['address'] : null);
            $attrs['geo'] = $geo;
            $attrs['crawl'] = $crawl;
            $lat = is_numeric($attrs['lat'] ?? null) ? (float) $attrs['lat'] : null;
            $lng = is_numeric($attrs['lng'] ?? null) ? (float) $attrs['lng'] : null;
            if ($lat !== null && $lng !== null) {
                $report['geo_filled']++;
            }
            $attrs['completeness'] = StayCompleteness::fromAttrs($attrs, $identity['source_hotel_key'] ?? null);
            if (count($report['rows']) < 50) {
                $report['rows'][] = [
                    'service_id' => $service->id,
                    'key' => $identity['source_hotel_key'],
                    'city' => $geo['city'],
                    'lat' => $lat,
                    'lng' => $lng,
                    'score' => $attrs['completeness']['score'],
                ];
            }
            if (! $dryRun) {
                $service->attrs = $attrs;
                if ($lat !== null) {
                    $service->lat = $lat;
                }
                if ($lng !== null) {
                    $service->lng = $lng;
                }
                $service->saveQuietly();
            }
        }

        $merged = $this->mergeAliases($dryRun);
        $report['amenity_aliases'] = $merged['amenity_aliases'];
        $report['place_aliases'] = $merged['place_aliases'];
        $report['amenity_merged'] = $merged['amenity_merged'];
        $report['place_merged'] = $merged['place_merged'];

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    public function assignAreas(bool $dryRun, int $limit = 0, bool $seedAreas = true): array
    {
        if ($seedAreas && ! $dryRun) {
            $this->areaSeed->seed();
        }
        $report = [
            'layer' => 'areas',
            'dry_run' => $dryRun,
            'matched' => 0,
            'unmatched' => 0,
            'rows' => [],
        ];
        $q = Service::withoutGlobalScopes()->where('cluster', Service::CLUSTER_STAY)->orderBy('id');
        if ($limit > 0) {
            $q->limit($limit);
        }
        $pool = \App\Models\StayArea::query()->where('is_active', true)->with('translations')->get();
        foreach ($q->cursor() as $service) {
            $attrs = is_array($service->attrs) ? $service->attrs : [];
            $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
            $lat = is_numeric($service->lat) ? (float) $service->lat : (is_numeric($attrs['lat'] ?? null) ? (float) $attrs['lat'] : null);
            $lng = is_numeric($service->lng) ? (float) $service->lng : (is_numeric($attrs['lng'] ?? null) ? (float) $attrs['lng'] : null);
            $area = $this->areas->resolve(
                (string) ($attrs['address'] ?? ''),
                isset($crawl['dest_id']) ? (string) $crawl['dest_id'] : null,
                $lat,
                $lng,
                $crawl['booking_cc'] ?? null,
                $pool,
            );
            if ($area) {
                $report['matched']++;
                if (! $dryRun) {
                    $service->stay_area_id = $area->id;
                    $service->saveQuietly();
                }
            } else {
                $report['unmatched']++;
                if (count($report['rows']) < 80) {
                    $report['rows'][] = [
                        'service_id' => $service->id,
                        'address' => $attrs['address'] ?? null,
                        'lat' => $lat,
                        'lng' => $lng,
                    ];
                }
            }
        }

        return $report;
    }

    /**
     * P6/P7: materialize stay_properties + attach stay_property_id (master = lowest service id per key).
     *
     * @return array<string, mixed>
     */
    public function materializeProperties(bool $dryRun, int $limit = 0): array
    {
        $report = ['layer' => 'properties', 'dry_run' => $dryRun, 'properties' => 0, 'projections' => 0, 'skipped' => 0];
        $q = Service::withoutGlobalScopes()
            ->where('cluster', Service::CLUSTER_STAY)
            ->orderBy('id');
        if ($limit > 0) {
            $q->limit($limit);
        }
        $seen = [];
        foreach ($q->with(['options', 'translations'])->cursor() as $service) {
            $attrs = is_array($service->attrs) ? $service->attrs : [];
            $key = data_get($attrs, 'crawl.source_hotel_key');
            if (! is_string($key) || $key === '') {
                $report['skipped']++;
                continue;
            }
            if (! $dryRun) {
                $property = $this->properties->upsertFromService($service);
                if ($property) {
                    if (! isset($seen[$key])) {
                        $seen[$key] = true;
                        $report['properties']++;
                    }
                    $service->stay_property_id = $property->id;
                    if (! $service->stay_area_id && $property->primary_stay_area_id) {
                        $service->stay_area_id = $property->primary_stay_area_id;
                    }
                    $service->saveQuietly();
                    $report['projections']++;
                }
            } else {
                $report['projections']++;
            }
        }

        return $report;
    }

    /**
     * R4: queue improve cho completeness thấp — không enqueue cả 10k.
     *
     * @return array<string, mixed>
     */
    public function queueImprove(bool $dryRun, int $limit = 0): array
    {
        $threshold = (int) config('stay.catalog.improve_score_below', 70);
        $max = $limit > 0 ? $limit : (int) config('stay.catalog.improve_batch', 40);
        $queued = [];
        $q = Service::withoutGlobalScopes()
            ->where('cluster', Service::CLUSTER_STAY)
            ->orderBy('id');
        foreach ($q->cursor() as $service) {
            if (count($queued) >= $max) {
                break;
            }
            $attrs = is_array($service->attrs) ? $service->attrs : [];
            $flags = StayCompleteness::fromAttrs($attrs, data_get($attrs, 'crawl.source_hotel_key'));
            if ($flags['score'] >= $threshold && $flags['geo'] && $flags['gallery']) {
                continue;
            }
            $from = ! $flags['geo'] || ! $flags['amenities'] ? 'basic' : (! $flags['gallery'] ? 'gallery' : 'rooms');
            $item = StayCrawlItem::withoutGlobalScopes()
                ->where('service_id', $service->id)
                ->orderByDesc('id')
                ->first();
            $queued[] = [
                'service_id' => $service->id,
                'item_id' => $item?->id,
                'score' => $flags['score'],
                'from' => $from,
            ];
            if (! $dryRun && $item) {
                $item->status = StayCrawlItem::STATUS_QUEUED;
                $item->error = null;
                $item->saveQuietly();
                $job = $item->job;
                if ($job) {
                    $meta = is_array($job->meta) ? $job->meta : [];
                    $meta['rerun'] = 'improve';
                    $meta['from'] = $from;
                    $job->meta = $meta;
                    $job->saveQuietly();
                }
                \App\Jobs\ProcessStayCrawlItemJob::dispatch((int) $item->id, 'vi', false, false);
            }
        }

        return [
            'layer' => 'improve',
            'dry_run' => $dryRun,
            'threshold' => $threshold,
            'queued' => count($queued),
            'rows' => $queued,
        ];
    }

    /**
     * @return array{amenity_aliases: int, place_aliases: int, amenity_merged: int, place_merged: int}
     */
    private function mergeAliases(bool $dryRun): array
    {
        $out = ['amenity_aliases' => 0, 'place_aliases' => 0, 'amenity_merged' => 0, 'place_merged' => 0];
        $langId = Language::idByCode('vi') ?: Language::query()->value('id');
        if (! $langId) {
            return $out;
        }

        $groups = [];
        foreach (StayAmenityTranslation::query()->where('language_id', $langId)->cursor() as $tr) {
            $fold = StayText::foldAmenity((string) $tr->name);
            if ($fold === '') {
                continue;
            }
            $groups[$fold][] = $tr;
        }
        foreach ($groups as $fold => $rows) {
            $masterId = (int) collect($rows)->min('stay_amenity_id');
            foreach ($rows as $tr) {
                if (! $dryRun) {
                    StayAmenityAlias::query()->updateOrCreate(
                        ['normalized' => $fold],
                        ['stay_amenity_id' => $masterId, 'alias' => $tr->name],
                    );
                    $out['amenity_aliases']++;
                }
                if ((int) $tr->stay_amenity_id !== $masterId) {
                    $out['amenity_merged']++;
                    if (! $dryRun) {
                        $this->repointAmenity((int) $tr->stay_amenity_id, $masterId);
                    }
                }
            }
        }

        $placeGroups = [];
        foreach (StayPlaceTranslation::query()->where('language_id', $langId)->cursor() as $tr) {
            $fold = StayText::fold((string) $tr->name);
            if ($fold === '') {
                continue;
            }
            $placeGroups[$fold][] = $tr;
        }
        foreach ($placeGroups as $fold => $rows) {
            $masterId = (int) collect($rows)->min('stay_place_id');
            foreach ($rows as $tr) {
                if (! $dryRun) {
                    StayPlaceAlias::query()->updateOrCreate(
                        ['normalized' => $fold],
                        ['stay_place_id' => $masterId, 'alias' => $tr->name],
                    );
                    $out['place_aliases']++;
                }
                if ((int) $tr->stay_place_id !== $masterId) {
                    $out['place_merged']++;
                    if (! $dryRun) {
                        $this->repointPlace((int) $tr->stay_place_id, $masterId);
                    }
                }
            }
        }

        return $out;
    }

    private function repointAmenity(int $fromId, int $toId): void
    {
        if ($fromId === $toId) {
            return;
        }
        DB::table('stay_amenity_service')->where('stay_amenity_id', $fromId)->orderBy('service_id')->each(function ($row) use ($toId, $fromId): void {
            $exists = DB::table('stay_amenity_service')
                ->where('service_id', $row->service_id)
                ->where('stay_amenity_id', $toId)
                ->exists();
            if ($exists) {
                DB::table('stay_amenity_service')->where('service_id', $row->service_id)->where('stay_amenity_id', $fromId)->delete();
            } else {
                DB::table('stay_amenity_service')->where('service_id', $row->service_id)->where('stay_amenity_id', $fromId)->update(['stay_amenity_id' => $toId]);
            }
        });
        StayAmenity::query()->whereKey($fromId)->delete();
    }

    private function repointPlace(int $fromId, int $toId): void
    {
        if ($fromId === $toId) {
            return;
        }
        DB::table('stay_place_service')->where('stay_place_id', $fromId)->orderBy('service_id')->each(function ($row) use ($toId, $fromId): void {
            $exists = DB::table('stay_place_service')
                ->where('service_id', $row->service_id)
                ->where('stay_place_id', $toId)
                ->exists();
            if ($exists) {
                DB::table('stay_place_service')->where('service_id', $row->service_id)->where('stay_place_id', $fromId)->delete();
            } else {
                DB::table('stay_place_service')->where('service_id', $row->service_id)->where('stay_place_id', $fromId)->update(['stay_place_id' => $toId]);
            }
        });
        StayPlace::query()->whereKey($fromId)->delete();
    }
}
