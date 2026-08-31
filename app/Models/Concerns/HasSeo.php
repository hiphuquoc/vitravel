<?php

namespace App\Models\Concerns;

use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public static function bootHasSeo(): void
    {
        static::deleting(function (Model $model): void {
            $entry = $model->seoEntry()->withoutGlobalScope('project')->first();
            if (! $entry) {
                return;
            }

            SeoEntryTranslation::withoutGlobalScope('project')
                ->where('seo_entry_id', $entry->id)
                ->delete();
            $entry->delete();
        });
    }

    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'reference');
    }
}
