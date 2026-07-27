<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleTranslation extends Model
{
    protected $fillable = [
        'article_id', 'language_id', 'title', 'excerpt', 'content', 'inline_related_links',
    ];

    protected function casts(): array
    {
        return ['inline_related_links' => 'array'];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
