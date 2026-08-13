<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Chuẩn hoá tên field listing giữa admin / AI / public chrome.
 *
 * Canonical (public ListingChrome + AI):
 * - title, subtitle, seo_body, banner
 *
 * Storage legacy vẫn giữ cột DB cũ; API accept cả hai tên.
 */
final class ListingFields
{
    /**
     * Merge alias → storage key nếu storage chưa gửi.
     *
     * @param  array<string, string>  $aliases  [storageKey => aliasKey]
     */
    public static function mergeAliases(Request $request, array $aliases): void
    {
        $merge = [];
        foreach ($aliases as $storage => $alias) {
            if (! $request->exists($storage) && $request->exists($alias)) {
                $merge[$storage] = $request->input($alias);
            }
        }
        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * @return array{subtitle: ?string, seo_body: ?string}
     */
    public static function chromeText(?string $subtitle, ?string $seoBody): array
    {
        return [
            'subtitle' => $subtitle,
            'seo_body' => $seoBody,
        ];
    }
}
