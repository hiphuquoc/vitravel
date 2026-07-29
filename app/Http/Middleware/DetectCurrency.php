<?php

namespace App\Http\Middleware;

use App\Services\CurrencyManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DetectCurrency — resolve sau DetectLocale.
 *
 * Ưu tiên: ?currency= → cookie → defaults_by_locale → currency.default
 */
class DetectCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        $manager = app(CurrencyManager::class);
        $cookieKey = (string) config('currency.cookie.name', 'app_currency');

        $resolved = null;
        $shouldPersistCookie = false;

        $queryCurrency = $request->query('currency');
        if (! empty($queryCurrency) && $manager->isSupported((string) $queryCurrency)) {
            $resolved = strtoupper((string) $queryCurrency);
            $shouldPersistCookie = true;
        }

        if ($resolved === null) {
            $cookieValue = $request->cookie($cookieKey);
            if (! empty($cookieValue) && $manager->isSupported((string) $cookieValue)) {
                $resolved = strtoupper((string) $cookieValue);
            }
        }

        if ($resolved === null) {
            $locale = app()->getLocale() ?: (string) config('language.default_code', 'vi');
            $resolved = $manager->defaultForLocale($locale);
        }

        $manager->setCurrent($resolved);
        $current = $manager->current();

        view()->share('currentCurrency', $current);
        view()->share('currentCurrencyMeta', $manager->currentMeta());
        view()->share('availableCurrencies', $manager->available());
        $request->attributes->set('currency', $current);

        /** @var Response $response */
        $response = $next($request);

        if ($shouldPersistCookie || empty($request->cookie($cookieKey))) {
            $minutes = (int) config('currency.cookie.ttl_days', 365) * 24 * 60;
            $sameSite = (string) config('currency.cookie.same_site', 'lax');
            $secure = config('currency.cookie.secure', null);
            $httpOnly = (bool) config('currency.cookie.http_only', false);

            try {
                $response->headers->setCookie(
                    cookie()->make(
                        $cookieKey,
                        $current,
                        $minutes,
                        '/',
                        null,
                        is_null($secure) ? $request->isSecure() : (bool) $secure,
                        $httpOnly,
                        false,
                        $sameSite
                    )
                );
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $response;
    }
}
