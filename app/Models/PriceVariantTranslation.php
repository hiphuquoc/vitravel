<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceVariantTranslation extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id', 'price_variant_id', 'language_id', 'name', 'description',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(PriceVariant::class, 'price_variant_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
