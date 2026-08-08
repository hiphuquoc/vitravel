<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeywordTagTranslation extends Model
{
    use BelongsToProject;

    protected $fillable = ['project_id', 'keyword_tag_id', 'language_id', 'label', 'slug'];

    public function keywordTag(): BelongsTo
    {
        return $this->belongsTo(KeywordTag::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
