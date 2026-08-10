<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedVideo extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id',
        'experience_video_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(ExperienceVideo::class, 'experience_video_id');
    }

    /** @return array<int, string> */
    public static function videoOptions(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $langId = Language::idByCode($locale);

        return ExperienceVideo::query()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->with('translations')
            ->get()
            ->mapWithKeys(function (ExperienceVideo $video) use ($langId) {
                $title = $video->translations->firstWhere('language_id', $langId)?->title
                    ?? $video->translations->first()?->title
                    ?? ('#'.$video->id);
                $duration = $video->duration ? " · {$video->duration}" : '';
                $status = $video->status !== 'published' ? ' · nháp' : '';
                $label = trim($title.$duration.$status);

                return [$video->id => $label];
            })
            ->all();
    }
}
