<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasMediaAttachments;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Review extends Model
{
    use BelongsToProject, HasMediaAttachments;

    protected $fillable = [
        'project_id', 'reviewable_type', 'reviewable_id', 'country_id', 'author_name',
        'author_country', 'author_country_code', 'avatar_media_id', 'rating',
        'reviewed_on', 'question_title', 'content', 'photos_count',
        'is_featured', 'show_on_home', 'status', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'photos_count' => 'integer',
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

    public function avatarUrl(?string $variant = 'thumb'): ?string
    {
        return app(MediaService::class)->publicUrl($this->avatar, $variant);
    }

    public function avatarSrcset(): ?string
    {
        return app(MediaService::class)->srcset($this->avatar);
    }

    /** Số ảnh hiển thị: ưu tiên gallery đã upload, không thì photos_count. */
    public function displayPhotosCount(): int
    {
        $attached = $this->relationLoaded('mediaAttachments')
            ? $this->mediaAttachments->where('role', 'gallery')->count()
            : $this->galleryAttachments()->count();

        return $attached > 0 ? $attached : (int) ($this->photos_count ?? 0);
    }

    /** @return list<string> */
    public function galleryUrls(int $limit = 3): array
    {
        $attachments = $this->relationLoaded('mediaAttachments')
            ? $this->mediaAttachments->where('role', 'gallery')->take($limit)
            : $this->galleryAttachments()->with('media')->limit($limit)->get();

        $media = app(MediaService::class);

        return $attachments
            ->map(fn ($a) => $media->publicUrl($a->media, 'thumb'))
            ->filter()
            ->values()
            ->all();
    }

    /** @return list<array{src: ?string, srcset: ?string, width: ?int, height: ?int, alt: ?string, variant: string}> */
    public function galleryPayloads(int $limit = 3, string $variant = 'thumb'): array
    {
        $attachments = $this->relationLoaded('mediaAttachments')
            ? $this->mediaAttachments->where('role', 'gallery')->take($limit)
            : $this->galleryAttachments()->with('media')->limit($limit)->get();

        $media = app(MediaService::class);

        return $attachments
            ->map(fn ($a) => $media->imagePayload($a->media, $variant))
            ->filter(fn (array $p) => filled($p['src'] ?? null))
            ->values()
            ->all();
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
