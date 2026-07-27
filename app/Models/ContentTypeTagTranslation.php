<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentTypeTagTranslation extends Model
{
    protected $fillable = ['content_type_tag_id', 'language_id', 'label', 'slug'];

    public function contentTypeTag(): BelongsTo
    {
        return $this->belongsTo(ContentTypeTag::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
