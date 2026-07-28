<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewPlatform extends Model
{
    protected $fillable = [
        'code', 'name', 'rating', 'review_count', 'url', 'quote', 'link_label',
        'logo_media_id', 'sort', 'is_active', 'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'sort' => 'integer',
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'logo_media_id');
    }
}
