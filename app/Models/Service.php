<?php

namespace App\Models;

use App\Models\Concerns\HasFaqs;
use App\Models\Concerns\HasMediaAttachments;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFaqs, HasMediaAttachments, HasSeo, HasTranslations, SoftDeletes;

    public const CLUSTER_TRAIN = 'train';

    public const CLUSTER_FLIGHT = 'flight';

    public const CLUSTER_STAY = 'stay';

    public const CLUSTER_EXPERIENCE = 'experience';

    public const CLUSTER_OTHER = 'other';

    /** @var list<string> */
    protected array $translatable = [
        'title', 'location_label', 'summary', 'highlights',
        'inclusions', 'exclusions', 'notes', 'content',
    ];

    protected $fillable = [
        'cluster', 'service_category_id', 'country_id', 'code',
        'price_from', 'currency', 'rating', 'review_count', 'star_rating',
        'is_featured', 'is_hot_deal', 'discount_badge', 'status', 'published_at',
        'view_count', 'sort', 'attrs',
    ];

    protected function casts(): array
    {
        return [
            'price_from' => 'decimal:2',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'star_rating' => 'integer',
            'is_featured' => 'boolean',
            'is_hot_deal' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'sort' => 'integer',
            'attrs' => 'array',
        ];
    }

    protected function translationClass(): string
    {
        return ServiceTranslation::class;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ServiceOption::class)->orderBy('sort');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeForCluster(Builder $query, string $cluster): Builder
    {
        return $query->where('cluster', $cluster);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function hubKey(): ?string
    {
        return config("services_catalog.clusters.{$this->cluster}.hub_key");
    }
}
