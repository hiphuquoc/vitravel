<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\PriceTable;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasPriceTable
{
    public function priceTable(): MorphOne
    {
        return $this->morphOne(PriceTable::class, 'priceable');
    }

    protected static function bootHasPriceTable(): void
    {
        static::forceDeleting(function ($model): void {
            $model->priceTable?->delete();
        });
    }
}
