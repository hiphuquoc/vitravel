<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationTranslation extends Model
{
    protected $fillable = ['destination_id', 'language_id', 'name', 'slug', 'intro_text'];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
