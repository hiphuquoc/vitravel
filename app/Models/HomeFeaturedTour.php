<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedTour extends Model
{
    use BelongsToProject;

    protected $fillable = [
        'project_id',
        'package_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /** @return array<int, string> */
    public static function tourOptions(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();
        $langId = Language::idByCode($locale);

        return Package::query()
            ->where('type', Package::TYPE_TOUR)
            ->orderBy('sort')
            ->orderByDesc('id')
            ->with(['translations', 'country.translations'])
            ->get()
            ->mapWithKeys(function (Package $package) use ($langId, $locale) {
                $title = $package->translations->firstWhere('language_id', $langId)?->title
                    ?? $package->translations->first()?->title
                    ?? $package->code;
                $country = $package->country?->translation($locale)?->name ?? '';
                $status = $package->status !== 'published' ? ' · nháp' : '';
                $label = trim($package->code.' — '.$title.($country ? " ({$country})" : '').$status);

                return [$package->id => $label];
            })
            ->all();
    }
}
