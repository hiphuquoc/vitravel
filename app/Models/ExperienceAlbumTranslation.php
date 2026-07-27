<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceAlbumTranslation extends Model
{
    protected $fillable = ['experience_album_id', 'language_id', 'title', 'description'];

    public function album(): BelongsTo
    {
        return $this->belongsTo(ExperienceAlbum::class, 'experience_album_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
