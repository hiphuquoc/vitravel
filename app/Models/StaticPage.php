<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class StaticPage extends Model
{
    use BelongsToProject, HasFaqs, HasSeo, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['title', 'body'];

    protected $fillable = ['project_id', 'template', 'banner_media_id', 'cover_media_id', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected function translationClass(): string
    {
        return StaticPageTranslation::class;
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function bannerUrl(?string $variant = 'lg'): ?string
    {
        return app(MediaService::class)->publicUrl($this->banner, $variant);
    }

    public function coverUrl(?string $variant = 'card'): ?string
    {
        return app(MediaService::class)->publicUrl($this->cover, $variant);
    }

    public function bannerSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->banner);
    }

    public function coverSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->cover);
    }
}
