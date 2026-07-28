<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedReviewPlatform extends Model
{
    protected $fillable = ['review_platform_id', 'sort'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(ReviewPlatform::class, 'review_platform_id');
    }

    /** @return array<int, string> */
    public static function platformOptions(): array
    {
        return ReviewPlatform::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get()
            ->mapWithKeys(fn (ReviewPlatform $p) => [
                $p->id => $p->name.($p->rating ? ' · '.$p->rating.'/5' : ''),
            ])
            ->all();
    }
}
