<?php

namespace App\Http\Controllers;

use App\Services\CurrencyManager;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request)
    {
        $manager = app(CurrencyManager::class);
        $to = strtoupper((string) ($request->input('to') ?: $request->query('to')));
        if ($to === '' || ! $manager->isSupported($to)) {
            $to = $manager->current();
        }

        $manager->setCurrent($to);

        $cookieName = (string) config('currency.cookie.name', 'app_currency');
        $minutes = (int) config('currency.cookie.ttl_days', 365) * 24 * 60;
        $sameSite = (string) config('currency.cookie.same_site', 'lax');
        $secure = config('currency.cookie.secure', null);
        $httpOnly = (bool) config('currency.cookie.http_only', false);

        $cookie = cookie(
            $cookieName,
            $to,
            $minutes,
            '/',
            null,
            is_null($secure) ? $request->isSecure() : (bool) $secure,
            $httpOnly,
            false,
            $sameSite
        );

        $redirect = $this->sanitizeRedirect(
            $request->input('redirect') ?: $request->query('redirect') ?: ($request->headers->get('referer') ?: '/')
        );

        return redirect($redirect)->withCookie($cookie);
    }

    private function sanitizeRedirect(?string $raw): string
    {
        if (empty($raw)) {
            return '/';
        }

        if (preg_match('#^https?://#i', $raw)) {
            $parts = parse_url($raw);
            $path = $parts['path'] ?? '/';
            $query = ! empty($parts['query']) ? '?'.$parts['query'] : '';
            $raw = $path.$query;
        }

        if (! str_starts_with($raw, '/')) {
            $raw = '/'.ltrim($raw, '/');
        }

        $raw = preg_replace('/([?&])currency=[^&]*(&|$)/', '$1', $raw) ?? $raw;
        $raw = rtrim($raw, '?&');

        return $raw === '' ? '/' : $raw;
    }
}
