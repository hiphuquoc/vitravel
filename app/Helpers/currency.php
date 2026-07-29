<?php

use App\Services\CurrencyManager;

if (! function_exists('currency_manager')) {
    function currency_manager(): CurrencyManager
    {
        return app(CurrencyManager::class);
    }
}

if (! function_exists('current_currency')) {
    function current_currency(): string
    {
        return currency_manager()->current();
    }
}

if (! function_exists('current_currency_meta')) {
    function current_currency_meta(): array
    {
        return currency_manager()->currentMeta();
    }
}

if (! function_exists('available_currencies')) {
    function available_currencies(): array
    {
        return currency_manager()->available();
    }
}

if (! function_exists('currency_symbol')) {
    function currency_symbol(?string $code = null): string
    {
        $meta = currency_manager()->meta($code);

        return (string) ($meta['symbol'] ?? '');
    }
}

if (! function_exists('convert_from_vnd')) {
    function convert_from_vnd($amountVnd, ?string $to = null): float
    {
        return currency_manager()->convertFromVnd($amountVnd, $to);
    }
}

if (! function_exists('format_price')) {
    /**
     * @param  array{currency?: string, html?: bool, fallback?: string}  $opts
     */
    function format_price($amountVnd, array $opts = []): string
    {
        if ($amountVnd === null || $amountVnd === '' || (is_numeric($amountVnd) && (float) $amountVnd <= 0)) {
            return (string) ($opts['fallback'] ?? config('currency.contact_label', 'Liên hệ'));
        }
        $to = $opts['currency'] ?? null;
        $html = array_key_exists('html', $opts) ? (bool) $opts['html'] : true;

        return currency_manager()->formatFromVnd($amountVnd, $to, $html);
    }
}

if (! function_exists('format_price_plain')) {
    function format_price_plain($amountVnd, ?string $to = null): string
    {
        if ($amountVnd === null || $amountVnd === '' || (is_numeric($amountVnd) && (float) $amountVnd <= 0)) {
            return (string) config('currency.contact_label', 'Liên hệ');
        }

        return currency_manager()->formatFromVnd($amountVnd, $to, false);
    }
}

if (! function_exists('currency_default_for_locale')) {
    function currency_default_for_locale(string $locale): string
    {
        return currency_manager()->defaultForLocale($locale);
    }
}

if (! function_exists('schema_currency')) {
    function schema_currency(?string $locale = null): string
    {
        return currency_default_for_locale($locale ?? app()->getLocale());
    }
}

if (! function_exists('schema_price_amount')) {
    function schema_price_amount($amountVnd, ?string $currency = null): float
    {
        if ($amountVnd === null || $amountVnd === '' || ! is_numeric($amountVnd)) {
            return 0.0;
        }
        $currency = $currency ?? schema_currency();

        return round(currency_manager()->convertFromVnd($amountVnd, $currency), 2);
    }
}

if (! function_exists('currency_rate_base')) {
    function currency_rate_base(): string
    {
        return currency_manager()->rateBase();
    }
}

if (! function_exists('rate_from_base')) {
    function rate_from_base(string $targetCode, ?string $baseCode = null): float
    {
        return currency_manager()->rateFromBase($targetCode, $baseCode);
    }
}

if (! function_exists('format_rate_from_base')) {
    function format_rate_from_base(string $targetCode, ?string $baseCode = null, bool $html = true): string
    {
        return currency_manager()->formatRateFromBase($targetCode, $baseCode, $html);
    }
}
