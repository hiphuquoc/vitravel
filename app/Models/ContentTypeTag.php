<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class ContentTypeTag extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['label', 'slug'];

    protected $fillable = ['project_id', 'code', 'sort', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return ContentTypeTagTranslation::class;
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_content_type_tag');
    }
}
