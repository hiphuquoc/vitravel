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

    /**
     * Options chọn trang cha SEO cho admin API / UI.
     *
     * @param  Collection<int, \App\Models\SeoEntry>  $parents
     * @return list<array{id: int, label: string, slug_full: string, reference_id: int|null, has_locale: bool}>
     */
    protected function mapSeoParents(Collection $parents, string $locale): array
    {
        $seo = $this->seoService();

        return $parents->map(function ($entry) use ($locale, $seo) {
            $exact = $entry->translationExact($locale);
            $fallback = $exact ?? $entry->translation($locale);
            $hasLocale = $exact && filled($exact->slug);
            // slug_full chỉ lấy đúng locale — không fallback EN/VI.
            $slugFull = $hasLocale
                ? (string) ($seo->resolveEntrySlugFull($entry, $locale) ?? '')
                : '';
            $title = (string) (
                $exact?->seo_title
                ?: $exact?->title
                ?: $fallback?->seo_title
                ?: $fallback?->title
                ?: ($slugFull !== '' ? $slugFull : '#'.$entry->id)
            );

            return [
                'id' => (int) $entry->id,
                'label' => $hasLocale && $slugFull !== ''
                    ? $slugFull
                    : ($slugFull !== '' ? $slugFull : ($title . ($hasLocale ? '' : ' — (chưa có bản dịch ' . $locale . ')'))),
                'slug_full' => $slugFull,
                'reference_id' => $entry->reference_id !== null ? (int) $entry->reference_id : null,
                'has_locale' => (bool) $hasLocale,
            ];
        })->values()->all();
    }

    /**
     * Locale codes đã có bản dịch nội dung (title/name không rỗng).
     *
     * @return list<string>
     */
    protected function translatedLocaleCodes(Model $model, string $labelField = 'title'): array
    {
        $model->loadMissing(['translations.language']);

        $codes = [];
        foreach ($model->translations as $row) {
            $code = $row->language?->code;
            if (! $code) {
                continue;
            }
            $label = $row->{$labelField} ?? null;
            if (is_string($label) && trim($label) !== '') {
                $codes[] = (string) $code;
            }
        }

        return array_values(array_unique($codes));
    }
}
