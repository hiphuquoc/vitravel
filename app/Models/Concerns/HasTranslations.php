<?php

namespace App\Models\Concerns;

use App\Models\Language;
use App\Support\LocaleContent;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslations
{
    abstract protected function translationClass(): string;

    public function translations(): HasMany
    {
        return $this->hasMany($this->translationClass());
    }

    public function translation(?string $locale = null): mixed
    {
        $locale = $locale ?: app()->getLocale();

        if ($this->relationLoaded('translations')) {
            return LocaleContent::firstTranslation($this->translations, $locale);
        }

        $ids = Language::contentLanguageIdChain($locale);
        if ($ids === []) {
            return null;
        }

        $rows = $this->translations()->whereIn('language_id', $ids)->get();

        return LocaleContent::firstTranslation($rows, $locale);
    }

    public function getAttribute($key): mixed
    {
        if (property_exists($this, 'translatable') && in_array($key, $this->translatable, true)) {
            return optional($this->translation())->{$key};
        }

        return parent::getAttribute($key);
    }
}
