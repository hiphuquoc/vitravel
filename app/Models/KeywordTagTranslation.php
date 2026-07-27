<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordTagTranslation extends Model
{
    protected $fillable = ['keyword_tag_id', 'language_id', 'label', 'slug'];

    public function keywordTag(): BelongsTo
    {
        return $this->belongsTo(KeywordTag::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
