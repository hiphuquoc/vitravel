<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HomeSection extends Model
{
    public const KEY_COMPANY_INTRO = 'company_intro';

    public const KEY_FEATURED_TOURS = 'featured_tours';

    public const KEY_FEATURED_CRUISES = 'featured_cruises';

    public const KEY_DESTINATIONS = 'destinations';

    public const KEY_TESTIMONIALS = 'testimonials';

    public const KEY_REVIEW_PLATFORMS = 'review_platforms';

    public const KEY_TEAM = 'team';

    public const KEY_VIDEOS = 'videos';

    public const KEY_QUICK_INQUIRY = 'quick_inquiry';

    /** @return array<string, string> */
    public static function keyLabels(): array
    {
        return [
            self::KEY_COMPANY_INTRO => 'Giới thiệu công ty',
            self::KEY_FEATURED_TOURS => 'Tour nổi bật',
            self::KEY_FEATURED_CRUISES => 'Du thuyền nổi bật',
            self::KEY_DESTINATIONS => 'Điểm đến',
            self::KEY_TESTIMONIALS => 'Cảm nhận khách hàng',
            self::KEY_REVIEW_PLATFORMS => 'Nền tảng đánh giá',
            self::KEY_TEAM => 'Đội ngũ',
            self::KEY_VIDEOS => 'Video trải nghiệm',
            self::KEY_QUICK_INQUIRY => 'Hỏi nhanh về tour',
        ];
    }

    /** @return list<string> */
    public static function predefinedKeys(): array
    {
        return array_keys(self::keyLabels());
    }

    /**
     * @return list<string>
     */
    public static function fieldsForKey(string $key): array
    {
        return match ($key) {
            self::KEY_COMPANY_INTRO => ['eyebrow', 'title', 'body', 'meta_line', 'cta_label', 'cta_url', 'image', 'image_alt'],
            self::KEY_FEATURED_TOURS, self::KEY_FEATURED_CRUISES, self::KEY_TESTIMONIALS, self::KEY_TEAM => ['eyebrow', 'title', 'subtitle', 'cta_label', 'cta_url'],
            self::KEY_DESTINATIONS => ['eyebrow', 'title', 'subtitle'],
            self::KEY_REVIEW_PLATFORMS => ['title'],
            self::KEY_VIDEOS => ['eyebrow', 'title', 'subtitle', 'cta_label', 'cta_url'],
            self::KEY_QUICK_INQUIRY => ['title', 'body'],
            default => ['title'],
        };
    }

    protected $fillable = [
        'key',
        'image_media_id',
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

    public function translations(): HasMany
    {
        return $this->hasMany(HomeSectionTranslation::class);
    }

    public function translation(?string $locale = null): ?HomeSectionTranslation
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

    public function label(): string
    {
        return self::keyLabels()[$this->key] ?? $this->key;
    }

    public function imageUrl(?string $variant = 'lg'): ?string
    {
        return app(MediaService::class)->publicUrl($this->image, $variant);
    }

    public function imageSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }
}
