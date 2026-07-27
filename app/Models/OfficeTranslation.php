<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTranslation extends Model
{
    protected $fillable = ['office_id', 'language_id', 'city_label', 'address_line'];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
