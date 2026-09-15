<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Language;
use App\Models\Service;
use App\Models\StayProperty;
use App\Models\StayPropertyTranslation;
use App\Models\StayTaxon;
use App\Support\StayCatalog\StayCompleteness;
use App\Support\StayCatalog\StayIdentity;
use Illuminate\Support\Str;

final class StayPropertySyncService
{
    public function __construct(private readonly StayAreaResolver $areas) {}

    public function upsertFromService(Service $service, ?int $taxonId = null): ?StayProperty
    {
        $attrs = is_array($service->attrs) ? $service->attrs : [];
        $crawl = is_array($attrs['crawl'] ?? null) ? $attrs['crawl'] : [];
        $url = (string) ($crawl['canonical_url'] ?? $crawl['source_url'] ?? '');
        $identity = $url !== '' ? StayIdentity::fromUrl($url) : [
            'source' => StayIdentity::SOURCE_BOOKING,
            'source_hotel_key' => $crawl['source_hotel_key'] ?? null,
            'booking_cc' => $crawl['booking_cc'] ?? null,
            'canonical_url' => $url,
        ];
        $key = $identity['source_hotel_key'] ?? null;
        if (! is_string($key) || $key === '') {
            return null;
        }

        $lat = is_numeric($service->lat ?? null) ? (float) $service->lat : (is_numeric($attrs['lat'] ?? null) ? (float) $attrs['lat'] : null);
        $lng = is_numeric($service->lng ?? null) ? (float) $service->lng : (is_numeric($attrs['lng'] ?? null) ? (float) $attrs['lng'] : null);
        $area = $this->areas->resolve(
            (string) ($attrs['address'] ?? ''),
            isset($crawl['dest_id']) ? (string) $crawl['dest_id'] : null,
            $lat,
            $lng,
            $identity['booking_cc'] ?? null,
        );

        $options = [];
        if ($service->relationLoaded('options') || $service->exists) {
            foreach ($service->options as $opt) {
                $options[] = [
                    'name' => $opt->translation()?->name ?? $opt->code,
                    'code' => $opt->code,
                    'price_from' => $opt->price_from,
                    'capacity' => $opt->capacity,
                    'attrs' => is_array($opt->attrs) ? $opt->attrs : [],
                ];
            }
        }
        $propAttrs = $attrs;
        if ($options !== []) {
            $propAttrs['options'] = $options;
        }
        $completeness = StayCompleteness::fromAttrs($propAttrs, $key, count($options));

        $property = StayProperty::query()->firstOrNew([
            'source' => $identity['source'] ?? StayIdentity::SOURCE_BOOKING,
            'source_hotel_key' => $key,
        ]);
        $property->fill([
            'booking_cc' => $identity['booking_cc'] ?? $property->booking_cc,
            'canonical_url' => (string) ($identity['canonical_url'] ?? $property->canonical_url ?: $url),
            'source_url' => (string) ($crawl['source_url'] ?? $property->source_url),
            'primary_stay_area_id' => $area?->id ?? $property->primary_stay_area_id,
            'country_code' => $area?->country_code
                ?: (isset($identity['booking_cc']) ? strtoupper((string) $identity['booking_cc']) : $property->country_code),
            'property_type' => $attrs['property_type'] ?? $property->property_type,
            'star_rating' => $service->star_rating ?? $property->star_rating,
            'rating' => $service->rating ?? $property->rating,
            'review_count' => $service->review_count ?? $property->review_count,
            'lat' => $lat ?? $property->lat,
            'lng' => $lng ?? $property->lng,
            'price_from' => $service->price_from ?? $property->price_from,
            'currency' => $service->currency ?: ($property->currency ?? 'VND'),
            'status' => 'published',
            'completeness' => $completeness,
            'last_crawled_at' => now(),
            'attrs' => $propAttrs,
        ]);
        $property->save();

        $langId = Language::idByCode(default_locale()) ?: Language::idByCode('vi');
        if ($langId) {
            StayPropertyTranslation::query()->updateOrCreate(
                ['stay_property_id' => $property->id, 'language_id' => $langId],
                [
                    'title' => (string) ($service->translation()?->title ?: $service->code ?: $key),
                    'location_label' => $service->translation()?->location_label,
                    'content' => $service->translation()?->content,
                ],
            );
        }

        if ($area) {
            $sync = [$area->id => ['role' => 'primary']];
            $parent = $area->parent;
            while ($parent) {
                if (! isset($sync[$parent->id])) {
                    $sync[$parent->id] = ['role' => 'parent'];
                }
                $parent = $parent->parent;
            }
            $property->areas()->syncWithoutDetaching($sync);
        }

        if ($taxonId && $taxonId > 0) {
            $this->attachTaxon($property, $taxonId);
        }

        return $property;
    }

    public function attachTaxon(StayProperty $property, int $taxonId): void
    {
        if ($taxonId <= 0 || ! StayTaxon::query()->whereKey($taxonId)->exists()) {
            return;
        }
        $property->taxons()->syncWithoutDetaching([$taxonId]);
    }

    public function mediaFolder(StayProperty $property, string $role = 'gallery'): string
    {
        $kind = Str::startsWith($role, 'cover') ? 'cover' : (Str::startsWith($role, 'room') ? 'room' : 'gallery');

        return 'catalog/stays/'.$property->id.'/'.$kind;
    }
}
