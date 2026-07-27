<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    protected array $supported;

    public function __construct()
    {
        $this->supported = array_keys(config('language', []));
    }

    public function handle(Request $request, Closure $next): Response
    {
        $default = Language::defaultCode();

        $requested = $request->query('lang') ?? $request->query('locale');
        if (is_string($requested) && $requested !== '' && in_array($requested, $this->supported, true)) {
            session(['locale' => $requested]);
        }

        $locale = (string) session('locale', $default);
        if (! in_array($locale, $this->supported, true)) {
            $locale = $default;
        }

        app()->setLocale($locale);
        app()->setFallbackLocale(Language::defaultCode());

        view()->share('currentLocale', $locale);

        $request->attributes->set('locale', $locale);

        return $next($request);
    }
}
