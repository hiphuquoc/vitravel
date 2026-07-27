<?php

namespace App\Models\Concerns;

use App\Models\SeoEntry;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'reference');
    }
}
