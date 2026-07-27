<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroPillTranslation extends Model
{
    protected $fillable = ['hero_pill_id', 'language_id', 'label'];

    public function heroPill(): BelongsTo
    {
        return $this->belongsTo(HeroPill::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
