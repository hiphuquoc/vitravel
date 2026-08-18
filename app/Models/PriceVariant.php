<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceVariant extends Model
{
    use BelongsToProject, HasTranslations;

    public const SOURCE_CUSTOM = 'custom';

    public const SOURCE_CABIN = 'cabin';

    public const SOURCE_SERVICE_OPTION = 'service_option';

    /** @var list<string> */
    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'project_id', 'price_table_id', 'code', 'source', 'source_id', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'source_id' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return PriceVariantTranslation::class;
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PriceTable::class, 'price_table_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(PriceRate::class, 'variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
