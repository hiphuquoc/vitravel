<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StayTaxonTranslation extends Model
{
    protected $fillable = [
        'stay_taxon_id',
        'language_id',
        'name',
        'slug',
    ];

    public function taxon(): BelongsTo
    {
        return $this->belongsTo(StayTaxon::class, 'stay_taxon_id');
    }
}
