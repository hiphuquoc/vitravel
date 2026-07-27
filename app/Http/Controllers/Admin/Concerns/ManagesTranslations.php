<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Language;
use App\Models\SeoEntryTranslation;
use App\Services\SeoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait ManagesTranslations
{
    protected function activeLanguages(): Collection
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();
    }

    protected function seoService(): SeoService
    {
        return app(SeoService::class);
    }

    /**
     * @param  list<string>  $fields
     */
    protected function saveModelTranslation(
        Model $model,
        string $translationClass,
        string $foreignKey,
        string $locale,
        array $data,
        array $fields,
    ): void {
        $languageId = Language::idByCode($locale);

        if (! $languageId) {
            return;
        }

        $payload = ['language_id' => $languageId];

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        $translationClass::query()->updateOrCreate(
            [$foreignKey => $model->id, 'language_id' => $languageId],
            $payload,
        );
    }

    /**
     * @param  array<string, array<string, mixed>>  $seoByLocale
     */
    protected function saveSeoTranslations(
        Model $model,
        array $seoByLocale,
        ?string $seoType = null,
        array $entryData = [],
    ): void {
        foreach ($seoByLocale as $locale => $seoData) {
            $this->seoService()->syncSeo($model, $locale, array_merge($seoData, $entryData), $seoType);
        }
    }

    /**
     * @return list<string>|null
     */
    protected function linesToArray(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $text)))));
    }

    protected function arrayToLines(mixed $value): string
    {
        if (! is_array($value)) {
            return '';
        }

        return implode("\n", $value);
    }

    protected function seoTypeForPackage(string $type): string
    {
        return $type === 'cruise' ? 'package_cruise' : 'package_tour';
    }
}
