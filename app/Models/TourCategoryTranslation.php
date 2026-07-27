<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourCategoryTranslation extends Model
{
    protected $fillable = [
        'tour_category_id', 'language_id', 'name', 'slug', 'description', 'seo_intro',
    ];

    public function tourCategory(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
