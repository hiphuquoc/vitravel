<?php

namespace App\Support;

use App\Models\Language;

/**
 * Helper chọn bản dịch theo chuỗi: locale hiện tại → EN → VI (mặc định).
 */
class LocaleContent
{
    /**
     * @param  array<string, mixed>  $byLocale  keyed by language code
     * @return array<string, mixed>|mixed
     */
    public static function pick(array $byLocale, ?string $locale = null, mixed $default = []): mixed
    {
        foreach (Language::contentLocaleChain($locale) as $code) {
            if (array_key_exists($code, $byLocale) && $byLocale[$code] !== null) {
                return $byLocale[$code];
            }
        }

        return $default;
    }

    /**
     * Tìm bản dịch đúng locale — không fallback EN/VI.
     * Dùng cho slug_full / URL hierarchy (không được mượn path ngôn ngữ khác).
     *
     * @param  iterable<int, object>  $translations
     */
    public static function firstTranslationExact(iterable $translations, ?string $locale = null): mixed
    {
        $locale = $locale ?: app()->getLocale();
        $languageId = Language::idByCode($locale);
        if (! $languageId) {
            return null;
        }

        foreach ($translations as $row) {
            if ((int) $row->language_id === (int) $languageId) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Tìm bản dịch trong collection/relation đã load theo language_id chain.
     *
     * @param  iterable<int, object>  $translations
     */
    public static function firstTranslation(iterable $translations, ?string $locale = null): mixed
    {
        $byId = [];
        foreach ($translations as $row) {
            $byId[(int) $row->language_id] = $row;
        }

        foreach (Language::contentLanguageIdChain($locale) as $id) {
            if (isset($byId[$id])) {
                return $byId[$id];
            }
        }

        return null;
    }
}
