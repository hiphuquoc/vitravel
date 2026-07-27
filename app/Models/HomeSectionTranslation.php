<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSectionTranslation extends Model
{
    protected $fillable = [
        'home_section_id',
        'language_id',
        'eyebrow',
        'title',
        'subtitle',
        'body',
        'meta_line',
        'cta_label',
        'cta_url',
        'image_alt',
    ];

    public function homeSection(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
