<?php

namespace App\Models\Concerns;

use App\Models\Media;
use App\Models\MediaAttachment;
use App\Services\MediaService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMediaAttachments
{
    public function mediaAttachments(): MorphMany
    {
        return $this->morphMany(MediaAttachment::class, 'mediable')->orderBy('sort');
    }

    public function galleryAttachments(): MorphMany
    {
        return $this->mediaAttachments()->where('role', 'gallery');
    }

    public function coverAttachment(): MorphMany
    {
        return $this->mediaAttachments()->where('role', 'cover');
    }

    public function coverMedia(): ?Media
    {
        $attachment = $this->relationLoaded('mediaAttachments')
            ? $this->mediaAttachments->firstWhere('role', 'cover')
            : $this->mediaAttachments()->where('role', 'cover')->with('media')->first();

        return $attachment?->media;
    }

    public function coverUrl(?string $variant = null): ?string
    {
        return app(MediaService::class)->publicUrl($this->coverMedia(), $variant);
    }

    public function coverSrcset(?array $variants = null): ?string
    {
        return app(MediaService::class)->srcset($this->coverMedia(), $variants);
    }

    /**
     * @return array{src: ?string, srcset: ?string, width: ?int, height: ?int, alt: ?string, variant: string}
     */
    public function coverPayload(string $variant = 'card'): array
    {
        return app(MediaService::class)->imagePayload($this->coverMedia(), $variant);
    }
}
