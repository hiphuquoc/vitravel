<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeFeaturedCountry extends Model
{
    use BelongsToProject;

    protected $fillable = ['project_id', 'country_id', 'sort'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /** @return array<int, string> */
    public static function countryOptions(?string $locale = null): array
    {
        $locale = $locale ?: app()->getLocale();

        return Country::query()
            ->active()
            ->orderBy('sort')
            ->with('translations')
            ->get()
            ->mapWithKeys(function (Country $country) use ($locale) {
                $name = $country->translation($locale)?->name ?? ('#'.$country->id);
                $size = $country->home_grid_size === 'large' ? ' · lưới lớn' : '';

                return [$country->id => $name.$size];
            })
            ->all();
    }
}
