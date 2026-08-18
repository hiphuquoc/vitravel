<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricePeriod extends Model
{
    use BelongsToProject;

    public const KIND_DATE = 'date';

    public const KIND_RANGE = 'range';

    public const KIND_YEAR = 'year';

    protected $fillable = [
        'project_id', 'price_table_id', 'kind', 'starts_on', 'ends_on', 'year',
        'label', 'is_promo', 'priority', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'year' => 'integer',
            'is_promo' => 'boolean',
            'priority' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class, 'price_table_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PriceRate::class, 'period_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function coversDate(CarbonInterface $date): bool
    {
        $day = $date->toDateString();

        return $this->starts_on->toDateString() <= $day
            && $this->ends_on->toDateString() >= $day;
    }
}
