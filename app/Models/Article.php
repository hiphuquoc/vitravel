<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFaqs, HasMediaAttachments, HasSeo, HasTranslations, SoftDeletes;

    /** @var list<string> */
    protected array $translatable = ['title', 'excerpt', 'content', 'inline_related_links'];

    protected $fillable = [
        'country_id', 'destination_id', 'blog_category_id', 'author_name',
        'rating', 'rating_count', 'view_count', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
            'view_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function translationClass(): string
    {
        return ArticleTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function blogCategory(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class);
    }

    public function contentTypeTags(): BelongsToMany
    {
        return $this->belongsToMany(ContentTypeTag::class, 'article_content_type_tag');
    }

    public function keywordTags(): BelongsToMany
    {
        return $this->belongsToMany(KeywordTag::class, 'article_keyword_tag');
    }

    public function relatedPackages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'article_package')
            ->withPivot('sort')
            ->orderByPivot('sort');
    }

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            Article::class,
            'article_related',
            'article_id',
            'related_article_id'
        )->withPivot('sort')->orderByPivot('sort');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('status', 'approved');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
