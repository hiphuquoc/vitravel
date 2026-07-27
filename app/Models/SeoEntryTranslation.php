<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoEntryTranslation extends Model
{
    protected $fillable = [
        'seo_entry_id', 'language_id', 'title', 'description',
        'seo_title', 'seo_description', 'keywords', 'slug', 'slug_full',
        'canonical_url', 'og_image_override', 'status', 'translation_status', 'published_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function seoEntry(): BelongsTo
    {
        return $this->belongsTo(SeoEntry::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
