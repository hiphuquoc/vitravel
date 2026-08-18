<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Package;
use App\Models\PriceGuestType;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Dựng payload bảng giá mẫu từ seed / giá “từ” / cabin / service option.
 */
final class PriceTableSample
{
    /**
     * @param  array<string, mixed>  $defaults
     * @param  array<string, mixed>|null  $explicit
     * @return array<string, mixed>|null
     */
    public static function payload(Model $priceable, array $defaults, ?array $explicit = null): ?array
    {
        if (is_array($explicit) && $explicit !== []) {
            return $explicit;
        }

        $year = (int) date('Y');
        $currency = strtoupper((string) ($priceable->getAttribute('currency') ?: 'VND'));
        $cluster = $priceable instanceof Service ? (string) $priceable->cluster : '';
        $unit = (string) ($defaults['cluster_units'][$cluster] ?? $defaults['unit'] ?? 'per_person');

        $variants = self::variants($priceable, (float) ($priceable->getAttribute('price_from') ?? 0));
        if ($variants === []) {
            return null;
        }

        $guests = PriceGuestType::query()->active()->get();
        if ($guests->isEmpty()) {
            return null;
        }

        $multipliers = $defaults['guest_multipliers'] ?? [
            'adult' => 1,
            'child' => 0.7,
            'senior' => 0.85,
        ];

        $periods = [];
        foreach ($defaults['periods'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $kind = (string) ($row['kind'] ?? 'year');
            $amountMul = (float) ($row['amount_multiplier'] ?? 1);
            $rates = [];
            foreach ($variants as $variant) {
                $base = (float) $variant['base_amount'];
                foreach ($guests as $guest) {
                    $guestMul = (float) ($multipliers[$guest->code] ?? 1);
                    $amount = self::roundMoney($base * $guestMul * $amountMul, $currency);
                    $compare = $amountMul < 1
                        ? self::roundMoney($base * $guestMul, $currency)
                        : null;
                    $rates[] = [
                        'variant_code' => $variant['code'],
                        'guest_type_code' => $guest->code,
                        'amount' => $amount,
                        'compare_at_amount' => $compare && $compare > $amount ? $compare : null,
                    ];
                }
            }

            $periods[] = [
                'kind' => $kind,
                'year' => $kind === 'year' ? $year : null,
                'starts_on' => isset($row['starts_on'])
                    ? str_replace('{year}', (string) $year, (string) $row['starts_on'])
                    : null,
                'ends_on' => isset($row['ends_on'])
                    ? str_replace('{year}', (string) $year, (string) $row['ends_on'])
                    : null,
                'label' => str_replace('{year}', (string) $year, (string) ($row['label'] ?? '')),
                'is_promo' => (bool) ($row['is_promo'] ?? false),
                'priority' => (int) ($row['priority'] ?? 0),
                'is_active' => true,
                'rates' => $rates,
            ];
        }

        if ($periods === []) {
            return null;
        }

        return [
            'currency' => $currency,
            'unit' => $unit,
            'notes' => $defaults['notes'] ?? null,
            'variants' => array_map(static function (array $v) {
                unset($v['base_amount']);

                return $v;
            }, $variants),
            'periods' => $periods,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function variants(Model $priceable, float $priceFrom): array
    {
        $base = $priceFrom > 0 ? $priceFrom : 500000.0;
        $out = [];

        if ($priceable instanceof Service) {
            $options = $priceable->relationLoaded('options')
                ? $priceable->options
                : $priceable->options()->with('translations')->get();
            foreach ($options as $i => $option) {
                $amount = $option->price_from !== null && (float) $option->price_from > 0
                    ? (float) $option->price_from
                    : $base;
                $name = (string) ($option->translation('vi')?->name
                    ?: $option->code
                    ?: 'Tuỳ chọn '.($i + 1));
                $out[] = [
                    'code' => $option->code ?: (Str::slug($name) ?: 'opt-'.$option->id),
                    'name' => $name,
                    'description' => $option->description,
                    'source' => 'service_option',
                    'source_id' => $option->id,
                    'sort' => $i,
                    'is_active' => true,
                    'base_amount' => $amount,
                ];
            }
        }

        if ($priceable instanceof Package) {
            $cabins = $priceable->relationLoaded('cabinTypes')
                ? $priceable->cabinTypes
                : $priceable->cabinTypes()->with('translations')->get();
            foreach ($cabins as $i => $cabin) {
                $name = (string) ($cabin->translation('vi')?->name ?: 'Cabin '.($i + 1));
                $out[] = [
                    'code' => Str::slug($name) ?: 'cabin-'.$cabin->id,
                    'name' => $name,
                    'description' => $cabin->description,
                    'source' => 'cabin',
                    'source_id' => $cabin->id,
                    'sort' => $i,
                    'is_active' => true,
                    'base_amount' => $base * (1 + (0.2 * $i)),
                ];
            }
        }

        if ($out === []) {
            $out[] = [
                'code' => 'standard',
                'name' => 'Tiêu chuẩn',
                'source' => 'custom',
                'source_id' => null,
                'sort' => 0,
                'is_active' => true,
                'base_amount' => $base,
            ];
        }

        return $out;
    }

    private static function roundMoney(float $amount, string $currency): float
    {
        if (strtoupper($currency) === 'VND') {
            return (float) (round($amount / 1000) * 1000);
        }

        return round($amount, 2);
    }
}
