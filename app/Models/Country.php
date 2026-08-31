<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Country extends Model
{
    use BelongsToProject, HasFaqs, HasMediaAttachments, HasSeo, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name', 'slug', 'tagline', 'intro_text', 'long_form_content'];

    protected $fillable = [
        'project_id', 'code', 'home_grid_size', 'banner_media_id', 'listing_banner_media_id', 'sort',
        'is_active', 'show_in_menu', 'show_in_customize_form',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_in_customize_form' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return CountryTranslation::class;
    }

    /** Thumbnail trang chủ / bento grid. */
    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function bannerUrl(?string $variant = 'card'): ?string
    {
        return app(MediaService::class)->publicUrl($this->banner, $variant);
    }

    public function bannerSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->banner);
    }

    /** Banner ngang dài — first-view trang listing /tours/{slug}. */
    public function listingBanner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'listing_banner_media_id');
    }

    public function listingBannerUrl(?string $variant = 'lg'): ?string
    {
        return app(MediaService::class)->publicUrl($this->listingBanner, $variant);
    }

    public function listingBannerSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->listingBanner);
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    /** Packages linked via pivot (multi-country) — không gồm chỉ country_id nếu chưa sync pivot. */
    public function linkedPackages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_country');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }
}
