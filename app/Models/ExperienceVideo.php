<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceVideo extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'country_id', 'youtube_id', 'video_url', 'thumbnail_media_id',
        'published_at', 'show_on_home', 'sort', 'status',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_on_home' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return ExperienceVideoTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }
}
