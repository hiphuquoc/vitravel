<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UspTranslation extends Model
{
    protected $fillable = ['usp_id', 'language_id', 'title', 'description'];

    public function usp(): BelongsTo
    {
        return $this->belongsTo(Usp::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
