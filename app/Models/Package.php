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
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use HasFaqs, HasMediaAttachments, HasSeo, HasTranslations, SoftDeletes;

    public const TYPE_TOUR = 'tour';

    public const TYPE_CRUISE = 'cruise';

    /** @var list<string> */
    protected array $translatable = [
        'title', 'start_location', 'end_location', 'places_to_visit',
        'featured_quote_text', 'featured_quote_author', 'highlights_intro',
        'highlight_bullets', 'inclusions', 'exclusions', 'notes', 'summary',
    ];

    protected $fillable = [
        'type', 'country_id', 'code', 'duration_days', 'duration_nights',
        'price_from', 'currency', 'rating', 'review_count',
        'is_featured', 'is_hot_deal', 'discount_badge', 'status', 'published_at',
        'view_count', 'sort', 'cruise_type', 'departure_port', 'boat_class', 'nights_on_board',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'duration_nights' => 'integer',
            'price_from' => 'decimal:2',
            'rating' => 'decimal:2',
            'review_count' => 'integer',
            'is_featured' => 'boolean',
            'is_hot_deal' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'sort' => 'integer',
            'nights_on_board' => 'integer',
        ];
    }

    protected function translationClass(): string
    {
        return PackageTranslation::class;
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** Loại du thuyền (SEO parent cho package_cruise) — khớp packages.cruise_type = cruise_types.slug. */
    public function cruiseType(): BelongsTo
    {
        return $this->belongsTo(CruiseType::class, 'cruise_type', 'slug');
    }

    /** Quốc gia gắn filter (tour kết hợp có thể nhiều). country_id = quốc gia chính URL/SEO. */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'package_country')
            ->withPivot('sort')
            ->orderByPivot('sort');
    }

    public function itineraryDays(): HasMany
    {
        return $this->hasMany(PackageItineraryDay::class)->orderBy('day_number');
    }

    public function cabinTypes(): HasMany
    {
        return $this->hasMany(PackageCabinType::class)->orderBy('sort');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(TourCategory::class, 'package_tour_category');
    }

    public function travelStyles(): BelongsToMany
    {
        return $this->belongsToMany(TravelStyle::class, 'package_travel_style');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class, 'package_destination');
    }

    public function relatedPackages(): BelongsToMany
    {
        return $this->belongsToMany(
            Package::class,
            'package_related',
            'package_id',
            'related_package_id'
        )->withPivot('sort')->orderByPivot('sort');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeTours(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_TOUR);
    }

    public function scopeCruises(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CRUISE);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeDurationBucket(Builder $query, string $bucket): Builder
    {
        return match ($bucket) {
            'lt7' => $query->where('duration_days', '<', 7),
            '7-10' => $query->whereBetween('duration_days', [7, 10]),
            '11-15' => $query->whereBetween('duration_days', [11, 15]),
            'gt16' => $query->where('duration_days', '>', 15),
            default => $query,
        };
    }

    public function isCruise(): bool
    {
        return $this->type === self::TYPE_CRUISE;
    }
}
