<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceVideoTranslation extends Model
{
    protected $fillable = ['experience_video_id', 'language_id', 'title', 'description'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(ExperienceVideo::class, 'experience_video_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
