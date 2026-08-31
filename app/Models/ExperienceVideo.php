<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ExperienceVideo extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'project_id', 'country_id', 'youtube_id', 'video_url', 'duration', 'tag',
        'thumbnail_media_id', 'video_media_id', 'published_at', 'show_on_home', 'sort', 'status',
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

    public function videoFile(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function thumbnailUrl(?string $variant = 'card'): ?string
    {
        $url = app(MediaService::class)->publicUrl($this->thumbnail, $variant);
        if ($url) {
            return $url;
        }

        $yt = $this->resolvedYoutubeId();
        if ($yt) {
            return 'https://i.ytimg.com/vi/'.$yt.'/hqdefault.jpg';
        }

        return null;
    }

    public function thumbnailSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->thumbnail);
    }

    public function videoFileUrl(): ?string
    {
        return app(MediaService::class)->publicUrl($this->videoFile);
    }

    public function resolvedYoutubeId(): ?string
    {
        return self::extractYoutubeId($this->youtube_id)
            ?? self::extractYoutubeId($this->video_url);
    }

    public function embedUrl(): ?string
    {
        $fileUrl = $this->videoFileUrl();
        if ($fileUrl) {
            return $fileUrl;
        }

        $yt = $this->resolvedYoutubeId();
        if ($yt) {
            return 'https://www.youtube.com/embed/'.$yt.'?autoplay=1&rel=0&modestbranding=1&playsinline=1';
        }

        $vimeo = self::extractVimeoId($this->video_url);
        if ($vimeo) {
            return 'https://player.vimeo.com/video/'.$vimeo.'?autoplay=1';
        }

        if (filled($this->video_url) && preg_match('/\.(mp4|webm|mov|m4v)(\?|$)/i', (string) $this->video_url)) {
            return $this->video_url;
        }

        if (filled($this->video_url) && str_contains((string) $this->video_url, '.mp4')) {
            return $this->video_url;
        }

        return null;
    }

    public function provider(): ?string
    {
        if ($this->video_media_id || $this->videoFileUrl()) {
            return 'file';
        }
        if ($this->resolvedYoutubeId()) {
            return 'youtube';
        }
        if (self::extractVimeoId($this->video_url)) {
            return 'vimeo';
        }
        if (filled($this->video_url) && preg_match('/\.(mp4|webm|mov|m4v)(\?|$)/i', (string) $this->video_url)) {
            return 'file';
        }
        if (filled($this->video_url)) {
            return 'file';
        }

        return null;
    }

    public static function extractYoutubeId(?string $input): ?string
    {
        if (! filled($input)) {
            return null;
        }

        $input = trim($input);
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $input, $m)) {
            return $m[1];
        }

        if (preg_match('/[?&]v=([a-zA-Z0-9_-]{11})/', $input, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function extractVimeoId(?string $input): ?string
    {
        if (! filled($input)) {
            return null;
        }

        if (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $input, $m)) {
            return $m[1];
        }

        return null;
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
