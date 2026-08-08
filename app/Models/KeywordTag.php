<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeywordTag extends Model
{
    use BelongsToProject, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['label', 'slug'];

    protected $fillable = ['project_id', 'target_url', 'weight', 'is_active'];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return KeywordTagTranslation::class;
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_keyword_tag');
    }
}
