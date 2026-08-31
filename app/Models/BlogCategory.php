<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BlogCategory extends Model
{
    use BelongsToProject, HasFaqs, HasSeo, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name', 'slug', 'seo_intro'];

    protected $fillable = [
        'project_id', 'level', 'country_id', 'destination_id', 'sort', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return BlogCategoryTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
