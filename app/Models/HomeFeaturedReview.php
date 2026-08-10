<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedReview extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id',
        'review_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    /** @return array<int, string> */
    public static function reviewOptions(?string $locale = null): array
    {
        unset($locale);

        return Review::query()
            ->orderBy('sort')
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(function (Review $review) {
                $trip = $review->question_title ? " · {$review->question_title}" : '';
                $status = $review->status !== 'published' ? ' · nháp' : '';
                $label = trim($review->author_name." ★{$review->rating}{$trip}{$status}");

                return [$review->id => $label];
            })
            ->all();
    }
}
