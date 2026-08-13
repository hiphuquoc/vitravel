<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroPill extends Model
{
    use BelongsToProject, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['label'];

    protected $fillable = [
        'project_id',
        'tour_category_id',
        'country_id',
        'target_url',
        'sort',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected function translationClass(): string
    {
        return HeroPillTranslation::class;
    }

    public function tourCategory(): BelongsTo
    {
        return $this->belongsTo(TourCategory::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function resolveUrl(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->tour_category_id) {
            $category = $this->relationLoaded('tourCategory')
                ? $this->tourCategory
                : $this->tourCategory()->with(['country.translations', 'translations'])->first();

            if ($category) {
                $countrySlug = $category->country?->translation($locale)?->slug;
                $categorySlug = $category->translation($locale)?->slug;

                if ($countrySlug && $categorySlug) {
                    return locale_route('tours.category', ['country' => $countrySlug, 'slug' => $categorySlug]);
                }
            }
        }

        if ($this->country_id) {
            $country = $this->relationLoaded('country')
                ? $this->country
                : $this->country()->with('translations')->first();

            $countrySlug = $country?->translation($locale)?->slug;

            if ($countrySlug) {
                return locale_route('tours.index', $countrySlug);
            }
        }

        if ($this->target_url) {
            return locale_route('tours.index', $this->target_url);
        }

        return '#';
    }

    public function resolveDefaultLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        if ($label = $this->translation($locale)?->label) {
            return $label;
        }

        if ($this->tour_category_id) {
            $category = $this->relationLoaded('tourCategory')
                ? $this->tourCategory
                : $this->tourCategory()->with('translations')->first();

            if ($category?->name) {
                return $category->name;
            }
        }

        if ($this->country_id) {
            $country = $this->relationLoaded('country')
                ? $this->country
                : $this->country()->with('translations')->first();

            if ($country?->name) {
                return $country->name;
            }
        }

        return '';
    }

    /** @return array<string, string> */
    public static function linkTargetOptions(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $options = [];

        $countries = Country::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->with('translations')
            ->get();

        foreach ($countries as $country) {
            $name = $country->translation($locale)?->name ?? $country->code;
            $options['country:'.$country->id] = 'Quốc gia — '.$name.' (tất cả tour)';
        }

        $categories = TourCategory::query()
            ->where('is_active', true)
            ->orderBy('country_id')
            ->orderBy('sort')
            ->with(['country.translations', 'translations'])
            ->get();

        foreach ($categories as $category) {
            $countryName = $category->country?->translation($locale)?->name ?? '';
            $categoryName = $category->translation($locale)?->name ?? '';
            $options['category:'.$category->id] = trim($countryName.' — '.$categoryName, ' —');
        }

        return $options;
    }

    public static function parseLinkTarget(?string $value): array
    {
        if (! $value || ! str_contains($value, ':')) {
            return ['tour_category_id' => null, 'country_id' => null];
        }

        [$type, $id] = explode(':', $value, 2);

        return match ($type) {
            'category' => ['tour_category_id' => (int) $id, 'country_id' => null],
            'country' => ['tour_category_id' => null, 'country_id' => (int) $id],
            default => ['tour_category_id' => null, 'country_id' => null],
        };
    }

    public function linkTargetValue(): string
    {
        if ($this->tour_category_id) {
            return 'category:'.$this->tour_category_id;
        }

        if ($this->country_id) {
            return 'country:'.$this->country_id;
        }

        return '';
    }
}
