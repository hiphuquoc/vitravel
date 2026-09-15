<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StayCategoryBinding extends Model
{
    public const SYNC_AUTO = 'auto';

    public const SYNC_MANUAL = 'manual';

    protected $fillable = [
        'service_category_id',
        'stay_area_id',
        'stay_taxon_id',
        'include_child_areas',
        'include_related_areas',
        'sync_mode',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'include_child_areas' => 'boolean',
            'include_related_areas' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(StayArea::class, 'stay_area_id');
    }

    public function taxon(): BelongsTo
    {
        return $this->belongsTo(StayTaxon::class, 'stay_taxon_id');
    }

    public function extraAreas(): BelongsToMany
    {
        return $this->belongsToMany(StayArea::class, 'stay_category_binding_areas');
    }
}
