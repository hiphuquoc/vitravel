<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSlideTranslation extends Model
{
    protected $fillable = [
        'home_slide_id',
        'language_id',
        'title',
        'title_accent',
        'description',
        'button_label',
        'image_alt',
    ];

    public function homeSlide(): BelongsTo
    {
        return $this->belongsTo(HomeSlide::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
