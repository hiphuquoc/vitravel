<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaticPage extends Model
{
    use HasFaqs, HasSeo, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['title', 'body'];

    protected $fillable = ['template', 'banner_media_id', 'status', 'published_at'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected function translationClass(): string
    {
        return StaticPageTranslation::class;
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'banner_media_id');
    }
}
