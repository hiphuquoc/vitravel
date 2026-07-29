<?php

namespace App\Services;

/**
 * CurrencyManager — giá gốc DB luôn VND; hiển thị theo cookie/locale.
 */
class CurrencyManager
{
    protected ?string $current = null;

    protected ?array $cachedCurrencies = null;

    public function setCurrent(string $code): void
    {
        $code = strtoupper(trim($code));
        $this->current = $this->isSupported($code) ? $code : $this->fallbackDefault();
    }

    public function current(): string
    {
        if ($this->current === null) {
            $this->current = $this->resolveFromRequest();
        }

        return $this->current;
    }

    protected function resolveFromRequest(): string
    {
        try {
            if (app()->bound('request')) {
                $req = app('request');
                $cookieName = (string) config('currency.cookie.name', 'app_currency');
                $value = $req->cookie($cookieName);
                if (! empty($value) && $this->isSupported((string) $value)) {
                    return strtoupper((string) $value);
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }

        return $this->resolveDefaultForCurrentLocale();
    }

    public function currentMeta(): array
    {
        return $this->meta($this->current());
    }

    public function meta(?string $code = null): array
    {
        $code = strtoupper(trim((string) $code));
        $all = $this->allCurrencies();
        if ($code !== '' && isset($all[$code])) {
            return $all[$code] + ['code' => $code];
        }
        $default = $this->fallbackDefault();

        return ($all[$default] ?? []) + ['code' => $default];
    }

    public function isSupported(string $code): bool
    {
        return isset($this->allCurrencies()[strtoupper($code)]);
    }

    /** Hỗ trợ zh-cn / zh-tw qua prefix trước dấu `-`. */
    public function defaultForLocale(string $locale): string
    {
        $map = (array) config('currency.defaults_by_locale', []);
        $locale = strtolower(trim($locale));
        $code = $map[$locale]
            ?? $map[explode('-', $locale)[0] ?? '']
            ?? $this->fallbackDefault();

        return $this->isSupported($code) ? strtoupper($code) : $this->fallbackDefault();
    }

    public function available(): array
    {
        $out = [];
        foreach ($this->allCurrencies() as $code => $meta) {
            if (! ($meta['enabled'] ?? true)) {
                continue;
            }
            $out[$code] = $meta + ['code' => $code];
        }

        return $out;
    }

    public function rateBase(): string
    {
        $code = strtoupper((string) config('currency.rate_base', 'USD'));

        return $this->isSupported($code) ? $code : $this->fallbackDefault();
    }

    public function rateFromBase(string $targetCode, ?string $baseCode = null): float
    {
        $base = $this->meta($baseCode ?? $this->rateBase());
        $target = $this->meta($targetCode);
        $baseVnd = max(1e-9, (float) ($base['vnd_per_unit'] ?? 1));
        $targetVnd = max(1e-9, (float) ($target['vnd_per_unit'] ?? 1));

        return $baseVnd / $targetVnd;
    }

    public function formatRateFromBase(string $targetCode, ?string $baseCode = null, bool $html = true): string
    {
        $target = $this->meta($targetCode);
        $rate = $this->rateFromBase($targetCode, $baseCode);
        $decimals = (int) ($target['decimals'] ?? 0);
        if ($rate < 1 && $decimals < 2) {
            $decimals = 2;
        }
        if ($rate >= 100) {
            $decimals = 0;
        }
        $thouSep = (string) ($target['thousands_sep'] ?? ',');
        $decSep = (string) ($target['decimal_sep'] ?? '.');
        $symbol = $html
            ? (string) ($target['symbol_html'] ?? ($target['symbol'] ?? ''))
            : (string) ($target['symbol'] ?? '');
        $gap = (string) config('currency.symbol_space', ' ');

        return number_format($rate, $decimals, $decSep, $thouSep).$gap.$symbol;
    }

    public function convertFromVnd($amountVnd, ?string $to = null): float
    {
        if ($amountVnd === null || $amountVnd === '') {
            return 0.0;
        }
        $amount = (float) $amountVnd;
        $meta = $this->meta($to ?? $this->current());
        $rate = max(1, (float) ($meta['vnd_per_unit'] ?? 1));

        return $amount / $rate;
    }

    public function format($amount, ?string $code = null, bool $html = true): string
    {
        $meta = $this->meta($code ?? $this->current());
        $decimals = (int) ($meta['decimals'] ?? 0);
        $thouSep = (string) ($meta['thousands_sep'] ?? ',');
        $decSep = (string) ($meta['decimal_sep'] ?? '.');
        $position = $meta['symbol_position'] ?? 'after';
        $symbol = $html
            ? (string) ($meta['symbol_html'] ?? ($meta['symbol'] ?? ''))
            : (string) ($meta['symbol'] ?? '');

        $value = (float) $amount;

        // Giá trị lớn: làm tròn lên và bỏ phần thập phân (vd $ 1,000.00 → $ 1,000)
        if ($decimals > 0 && $this->shouldDropFractionals($value)) {
            $value = $value >= 0 ? ceil($value) : floor($value);
            $decimals = 0;
        }

        $num = number_format($value, $decimals, $decSep, $thouSep);
        $gap = (string) config('currency.symbol_space', ' ');

        if ($symbol === '') {
            return $num;
        }

        // Luôn cách 1 khoảng giữa số và ký hiệu (1.000.000 đ / $ 12.50)
        return $position === 'before'
            ? $symbol.$gap.$num
            : $num.$gap.$symbol;
    }

    /** Số đủ lớn để bỏ .xx khi hiển thị (ngưỡng config currency.round_large_threshold). */
    protected function shouldDropFractionals(float $amount): bool
    {
        if (! (bool) config('currency.round_large_enabled', true)) {
            return false;
        }

        $threshold = (float) config('currency.round_large_threshold', 100);

        return abs($amount) >= $threshold;
    }

    public function formatFromVnd($amountVnd, ?string $to = null, bool $html = true): string
    {
        if ($amountVnd === null || $amountVnd === '') {
            return (string) config('currency.contact_label', 'Liên hệ');
        }

        $code = $to ?? $this->current();
        $value = $this->convertFromVnd($amountVnd, $code);
        $min = (float) config('currency.min_display', 0);

        if ($min > 0 && $value > 0 && $value < $min) {
            return (string) config('currency.contact_label', 'Liên hệ');
        }

        return $this->format($value, $code, $html);
    }

    protected function fallbackDefault(): string
    {
        $default = strtoupper((string) config('currency.default', 'VND'));
        $all = $this->allCurrencies();
        if (isset($all[$default])) {
            return $default;
        }
        $first = array_key_first($all);

        return $first ? strtoupper($first) : 'VND';
    }

    protected function resolveDefaultForCurrentLocale(): string
    {
        try {
            $locale = app()->getLocale() ?: (string) config('language.default_code', 'vi');

            return $this->defaultForLocale($locale);
        } catch (\Throwable $e) {
            return $this->fallbackDefault();
        }
    }

    protected function allCurrencies(): array
    {
        if ($this->cachedCurrencies !== null) {
            return $this->cachedCurrencies;
        }
        $list = (array) config('currency.currencies', []);
        $norm = [];
        foreach ($list as $code => $meta) {
            $norm[strtoupper((string) $code)] = is_array($meta) ? $meta : [];
        }

        return $this->cachedCurrencies = $norm;
    }
}
