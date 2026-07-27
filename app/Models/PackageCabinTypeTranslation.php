<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageCabinTypeTranslation extends Model
{
    protected $fillable = [
        'package_cabin_type_id', 'language_id', 'name', 'description',
    ];

    public function cabinType(): BelongsTo
    {
        return $this->belongsTo(PackageCabinType::class, 'package_cabin_type_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
