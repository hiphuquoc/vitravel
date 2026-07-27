<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageItineraryDay extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['title', 'content', 'overnight_at', 'internal_links'];

    protected $fillable = [
        'package_id', 'day_number', 'meals_included', 'transport_icons',
        'distance_info', 'image_media_id', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'transport_icons' => 'array',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return PackageItineraryDayTranslation::class;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }
}
