<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaAttachment extends Model
{
    protected $fillable = [
        'mediable_type', 'mediable_id', 'media_id', 'role', 'caption', 'sort',
    ];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
