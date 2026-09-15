<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\Service;
use App\Models\StayProperty;
use App\Support\StayFacilities;

/**
 * Overlay facts catalog lên projection (P9).
 */
final class StayCatalogFacts
{
    /**
     * @return array<string, mixed>
     */
    public static function attrs(Service $service): array
    {
        $local = is_array($service->attrs) ? $service->attrs : [];
        if (! (bool) config('stay.catalog.enabled', false)) {
            return $local;
        }
        $property = $service->relationLoaded('stayProperty')
            ? $service->stayProperty
            : ($service->stay_property_id ? StayProperty::query()->find($service->stay_property_id) : null);
        if (! $property) {
            return $local;
        }
        $catalog = is_array($property->attrs) ? $property->attrs : [];
        if ($catalog === []) {
            return $local;
        }

        return StayFacilities::overlayRicherStayAttrs($local, $catalog);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function optionRows(Service $service): array
    {
        $hasLocalRooms = $service->relationLoaded('options')
            ? $service->options->isNotEmpty()
            : $service->options()->exists();
        if ($hasLocalRooms) {
            return [];
        }
        if (! (bool) config('stay.catalog.enabled', false)) {
            return [];
        }
        $property = $service->relationLoaded('stayProperty')
            ? $service->stayProperty
            : ($service->stay_property_id ? StayProperty::query()->find($service->stay_property_id) : null);
        $opts = is_array($property?->attrs['options'] ?? null) ? $property->attrs['options'] : [];

        return array_values(array_filter($opts, 'is_array'));
    }
}
