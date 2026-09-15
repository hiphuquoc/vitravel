<?php

declare(strict_types=1);

namespace App\Services\StayCatalog;

use App\Models\ServiceCategory;
use App\Models\StayArea;
use App\Models\StayCategoryBinding;
use App\Models\StayTaxon;
use Illuminate\Validation\ValidationException;

final class StayCategoryBindService
{
    public function __construct(private readonly StayCatalogProjectionService $projections) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function bind(ServiceCategory $category, array $payload, bool $sync = true): StayCategoryBinding
    {
        if ($category->cluster !== 'stay') {
            throw ValidationException::withMessages(['cluster' => 'Chỉ bind danh mục lưu trú.']);
        }
        $areaId = (int) ($payload['stay_area_id'] ?? 0);
        if ($areaId <= 0 || ! StayArea::query()->whereKey($areaId)->exists()) {
            throw ValidationException::withMessages(['stay_area_id' => 'Khu vực catalog không hợp lệ.']);
        }
        $taxonId = isset($payload['stay_taxon_id']) ? (int) $payload['stay_taxon_id'] : null;
        if ($taxonId && ! StayTaxon::query()->whereKey($taxonId)->exists()) {
            throw ValidationException::withMessages(['stay_taxon_id' => 'Taxon không hợp lệ.']);
        }

        $binding = StayCategoryBinding::query()->updateOrCreate(
            [
                'service_category_id' => $category->id,
                'stay_area_id' => $areaId,
                'stay_taxon_id' => $taxonId ?: null,
            ],
            [
                'include_child_areas' => (bool) ($payload['include_child_areas'] ?? true),
                'include_related_areas' => (bool) ($payload['include_related_areas'] ?? false),
                'sync_mode' => (string) ($payload['sync_mode'] ?? StayCategoryBinding::SYNC_AUTO),
            ],
        );

        $extra = array_values(array_filter(array_map('intval', (array) ($payload['extra_area_ids'] ?? []))));
        if ($extra !== []) {
            $binding->extraAreas()->sync($extra);
        }

        if ($sync && $binding->sync_mode === StayCategoryBinding::SYNC_AUTO) {
            $this->projections->syncBinding($binding->fresh(['area', 'taxon', 'extraAreas', 'category']) ?? $binding);
        }

        return $binding->fresh(['area.translations', 'taxon.translations', 'extraAreas']) ?? $binding;
    }

    public function unbind(StayCategoryBinding $binding): void
    {
        $binding->extraAreas()->detach();
        $binding->delete();
    }
}
