<?php

namespace App\Models;

use App\Models\Concerns\HasMediaAttachments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasMediaAttachments, SoftDeletes;

    protected $fillable = [
        'reviewable_type', 'reviewable_id', 'country_id', 'author_name',
        'author_country', 'author_country_code', 'avatar_media_id', 'rating',
        'reviewed_on', 'question_title', 'content', 'is_featured',
        'show_on_home', 'status', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'reviewed_on' => 'date',
            'is_featured' => 'boolean',
            'show_on_home' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeForHome(Builder $query): Builder
    {
        return $query->published()->where('show_on_home', true)->orderBy('sort');
    }
}
