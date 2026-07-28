<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeSlide extends Model
{
    use SoftDeletes;

    public const ALIGN_LEFT = 'left';

    public const ALIGN_CENTER = 'center';

    public const ALIGN_RIGHT = 'right';

    /** @return array<string, string> */
    public static function alignOptions(): array
    {
        return [
            self::ALIGN_LEFT => 'Trái',
            self::ALIGN_CENTER => 'Giữa',
            self::ALIGN_RIGHT => 'Phải',
        ];
    }

    protected $fillable = [
        'image_media_id',
        'image_mobile_media_id',
        'text_align',
        'link_url',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_media_id');
    }

    public function imageMobile(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_mobile_media_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(HomeSlideTranslation::class);
    }

    public function translation(?string $locale = null): ?HomeSlideTranslation
    {
        $locale = $locale ?: app()->getLocale();
        $langId = Language::idByCode($locale);

        $match = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('language_id', $langId)
            : $this->translations()->where('language_id', $langId)->first();

        if ($match) {
            return $match;
        }

        $defaultId = Language::defaultId();
        if ($defaultId && $defaultId !== $langId) {
            return $this->relationLoaded('translations')
                ? $this->translations->firstWhere('language_id', $defaultId)
                : $this->translations()->where('language_id', $defaultId)->first();
        }

        return null;
    }

    public function imageUrl(?string $variant = 'full'): ?string
    {
        return app(MediaService::class)->publicUrl($this->image, $variant);
    }

    public function imageSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->image);
    }

    public function imageMobileUrl(?string $variant = 'lg'): ?string
    {
        return app(MediaService::class)->publicUrl($this->imageMobile, $variant)
            ?? $this->imageUrl($variant === 'lg' ? 'lg' : $variant);
    }

    public function imageMobileSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->imageMobile)
            ?? $this->imageSrcset();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }
}
