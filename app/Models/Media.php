<?php

namespace App\Models;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'filename', 'mime_type', 'size_bytes',
        'width', 'height', 'alt', 'credit', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MediaAttachment::class);
    }

    /** URL theo variant: thumb|card|lg|full (+ aliases trong config/media.php). */
    public function url(?string $variant = null): string
    {
        return app(MediaService::class)->publicUrl($this, $variant) ?? '/storage/'.$this->path;
    }

    public function srcset(?array $variants = null): ?string
    {
        return app(MediaService::class)->srcset($this, $variants);
    }

    /**
     * @return array{src: ?string, srcset: ?string, width: ?int, height: ?int, alt: ?string, variant: string}
     */
    public function payload(string $variant = 'card'): array
    {
        return app(MediaService::class)->imagePayload($this, $variant);
    }

    public function hasVariants(): bool
    {
        return ! empty($this->meta['variants']);
    }
}
