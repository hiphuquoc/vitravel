<?php

namespace App\Models\Concerns;

use App\Models\Language;
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
        $langId = Language::idByCode($locale);

        $match = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('language_id', $langId)
            : $this->translations()->where('language_id', $langId)->first();

        if ($match) {
            return $match;
        }

        $defaultId = Language::defaultId();
        if ($defaultId && $defaultId !== $langId) {
            return $this->relationLoaded('translations')
                ? $this->translations->firstWhere('language_id', $defaultId)
                : $this->translations()->where('language_id', $defaultId)->first();
        }

        return null;
    }

    public function getAttribute($key): mixed
    {
        if (property_exists($this, 'translatable') && in_array($key, $this->translatable, true)) {
            return optional($this->translation())->{$key};
        }

        return parent::getAttribute($key);
    }
}
