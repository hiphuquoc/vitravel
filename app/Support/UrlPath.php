<?php

namespace App\Support;

use App\Models\Language;

class UrlPath
{
    /**
     * Chuẩn hoá path: bỏ segment rỗng / "public", tách locale prefix.
     *
     * @return array{0: string|null, 1: list<string>}
     */
    public static function cleanRequestPathWithLocale(string $path): array
    {
        $tmp = explode('/', $path);
        $clean = array_values(array_filter($tmp, fn ($s) => $s !== '' && $s !== 'public'));

        if ($clean !== []) {
            // Strip query/hash leftovers on last segment (escape # inside #…# delimiters).
            $clean[count($clean) - 1] = (string) preg_replace('#([\?\#]+).*$#imsU', '', (string) end($clean));
        }

        $locale = null;
        if ($clean !== [] && self::isLocaleSegment($clean[0])) {
            $locale = array_shift($clean);
        }

        return [$locale, $clean];
    }

    private static function isLocaleSegment(string $code): bool
    {
        try {
            if (Language::byCode($code)) {
                return true;
            }
        } catch (\Throwable $e) {
            // DB unavailable
        }

        $list = config('language.list', []);

        return isset($list[$code]) && empty($list[$code]['is_default']);
    }

    /**
     * @return list<string>
     */
    public static function cleanRequestPath(string $path): array
    {
        [, $clean] = self::cleanRequestPathWithLocale($path);

        return $clean;
    }
}
