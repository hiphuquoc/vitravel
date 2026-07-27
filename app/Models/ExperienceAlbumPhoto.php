<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceAlbumPhoto extends Model
{
    protected $fillable = ['experience_album_id', 'media_id', 'caption', 'sort'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(ExperienceAlbum::class, 'experience_album_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
