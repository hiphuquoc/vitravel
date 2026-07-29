<?php

namespace App\Http\Controllers\Concerns;

use App\Services\HtmlCacheService;
use Illuminate\Http\Response;

trait RendersWithHtmlCache
{
    /**
     * Render HTML qua HtmlCacheService — key luôn kèm currency (và locale từ URL).
     *
     * @param  callable(): string  $render
     * @param  array<string, mixed>  $queryParams
     */
    protected function cachedHtmlResponse(callable $render, array $queryParams = [], bool $homepage = false): Response
    {
        $key = $homepage
            ? HtmlCacheService::homepageCacheKey()
            : HtmlCacheService::buildKeyFromRequest(request(), $queryParams);

        $currency = function_exists('current_currency') ? current_currency() : 'VND';
        $key .= '-'.strtolower($currency);

        $html = app(HtmlCacheService::class)->getOrRender($key, $render, $homepage);

        return response($html ?? '');
    }
}
