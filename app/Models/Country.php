<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFaqs, HasMediaAttachments, HasSeo, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['name', 'slug', 'tagline', 'intro_text', 'long_form_content'];

    protected $fillable = [
        'code', 'home_grid_size', 'banner_media_id', 'sort',
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

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function bannerUrl(): ?string
    {
        return app(MediaService::class)->publicUrl($this->banner);
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort');
    }
}
