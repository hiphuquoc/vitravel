<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceOption extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected array $translatable = [
        'name', 'description', 'amenities',
    ];

    protected $fillable = [
        'service_id', 'code', 'price_from', 'capacity', 'sort', 'attrs',
    ];

    protected function casts(): array
    {
        return [
            'price_from' => 'decimal:2',
            'capacity' => 'integer',
            'sort' => 'integer',
            'attrs' => 'array',
        ];
    }

    protected function translationClass(): string
    {
        return ServiceOptionTranslation::class;
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return BelongsToMany<StayAmenity> */
    public function stayAmenities(): BelongsToMany
    {
        return $this->belongsToMany(StayAmenity::class, 'stay_amenity_service_option')
            ->withPivot(['sort'])
            ->orderByPivot('sort');
    }
}
