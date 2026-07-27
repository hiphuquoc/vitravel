<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceAlbum extends Model
{
    use HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'country_id', 'cover_media_id', 'customer_name', 'trip_date',
        'photo_count', 'sort', 'status',
    ];

    protected function casts(): array
    {
        return [
            'trip_date' => 'date',
            'photo_count' => 'integer',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return ExperienceAlbumTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ExperienceAlbumPhoto::class)->orderBy('sort');
    }
}
