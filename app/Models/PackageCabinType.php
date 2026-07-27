<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageCabinType extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name', 'description'];

    protected $fillable = [
        'package_id', 'capacity', 'price_from', 'currency', 'amenities', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_from' => 'decimal:2',
            'amenities' => 'array',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return PackageCabinTypeTranslation::class;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
