<?php

namespace App\Models;

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

    public function url(): string
    {
        return app(\App\Services\MediaService::class)->publicUrl($this) ?? '/storage/'.$this->path;
    }
}
