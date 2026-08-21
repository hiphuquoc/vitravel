<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;

/**
 * Chuẩn hoá copy giá / quyền lợi / scarcity lưu trú — dùng chung crawler, admin, public.
 */
final class StayRateCopy
{
    public const DEFAULT_DEAL_KEY = 'seasonal';

    public const SCARCITY_MIN = 1;

    public const SCARCITY_MAX = 5;

    /** @return array<string, string> key => nhãn hiển thị */
    public static function dealLabels(): array
    {
        $labels = config('stay.deal_labels', []);

        return is_array($labels) ? array_map('strval', $labels) : [];
    }

    public static function dealLabel(?string $key): string
    {
        $key = trim((string) $key);
        if ($key === '') {
            $key = self::DEFAULT_DEAL_KEY;
        }
        $labels = self::dealLabels();

        return $labels[$key] ?? ($labels[self::DEFAULT_DEAL_KEY] ?? 'Ưu Đãi Mùa Du Lịch');
    }

    /** Còn scarcity động (random 1–5 căn lúc hiển thị — thân thiện HTML cache). */
    public static function scarcityActive(array $attrs): bool
    {
        if (array_key_exists('scarcity_active', $attrs)) {
            return (bool) $attrs['scarcity_active'];
        }
        if (! empty($attrs['availability_left']) && (int) $attrs['availability_left'] > 0) {
            return true;
        }
        $raw = trim((string) ($attrs['scarcity'] ?? ''));

        return $raw !== '' && (bool) preg_match('/\d+/', $raw);
    }

    public static function scarcityTemplate(): string
    {
        return (string) config('stay.scarcity_template', 'Chúng tôi còn {n} phòng');
    }

    /** % tiết kiệm từ giá gạch vs giá bán (cùng đơn vị: tổng kỳ hoặc /đêm). */
    public static function savePercent(float|int|string|null $price, float|int|string|null $strike): ?int
    {
        if (! is_numeric($price) || ! is_numeric($strike)) {
            return null;
        }
        $price = (float) $price;
        $strike = (float) $strike;
        if ($price <= 0 || $strike <= $price) {
            return null;
        }
        $pct = (int) round((1 - ($price / $strike)) * 100);

        return ($pct >= 1 && $pct <= 95) ? $pct : null;
    }

    /**
     * Chuẩn hoá tiêu đề hủy — bỏ ngày tuyệt đối crawler → “trước N ngày”.
     *
     * @param  array{checkin?: string}|null  $crawlDates
     */
    public static function normalizeCancellationTitle(string $title, ?array $crawlDates = null): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? $title);
        if ($title === '') {
            return '';
        }

        if (preg_match('/không\s+hoàn\s+tiền|non[\s-]?refundable/iu', $title)) {
            return 'Không hoàn tiền';
        }

        if (preg_match('/hủy\s+miễn\s+phí\s+trước\s+(\d+)\s+ngày/iu', $title, $m)) {
            $n = max(1, (int) $m[1]);

            return $n === 1 ? 'Hủy miễn phí trước 1 ngày' : "Hủy miễn phí trước {$n} ngày";
        }

        $deadline = self::extractVietnameseDate($title);
        $checkin = trim((string) ($crawlDates['checkin'] ?? ''));
        if ($deadline !== null && $checkin !== '') {
            try {
                $checkinDay = Carbon::parse($checkin)->startOfDay();
                $deadlineDay = $deadline->copy()->startOfDay();
                if ($deadlineDay->lt($checkinDay)) {
                    $days = (int) $deadlineDay->diffInDays($checkinDay);
                    if ($days > 0) {
                        return $days === 1
                            ? 'Hủy miễn phí trước 1 ngày'
                            : "Hủy miễn phí trước {$days} ngày";
                    }
                }
            } catch (\Throwable) {
                // fall through
            }
        }

        if ($deadline !== null && preg_match('/hủy\s+miễn\s+phí/iu', $title)) {
            return 'Hủy miễn phí trước vài ngày';
        }

        if (preg_match('/hủy\s+miễn\s+phí/iu', $title)) {
            return 'Hủy miễn phí';
        }

        return $title;
    }

    public static function normalizePrepaymentTitle(string $title): string
    {
        $title = trim(preg_replace('/\s+/u', ' ', $title) ?? $title);
        if ($title === '') {
            return '';
        }

        $replacements = [
            '/^thanh\s+toán\s+cho\s+chỗ\s+nghỉ\s+trước\s+khi\s+đến\.?$/iu' => 'Thanh toán trước khi đến',
            '/^pay\s+the\s+(property|hotel)\s+before\s+arrival\.?$/iu' => 'Thanh toán trước khi đến',
            '/^no\s+prepayment\s+needed\.?$/iu' => 'Không cần thanh toán trước',
            '/^không\s+cần\s+thanh\s+toán\s+trước\.?$/iu' => 'Không cần thanh toán trước',
        ];
        foreach ($replacements as $pattern => $label) {
            if (preg_match($pattern, $title)) {
                return $label;
            }
        }

        // Rút gọn cụm dài phổ biến
        $title = preg_replace('/thanh\s+toán\s+cho\s+chỗ\s+nghỉ\s+trước\s+khi\s+đến/iu', 'Thanh toán trước khi đến', $title) ?? $title;

        return trim($title);
    }

    /**
     * Gắn save_percent + deal_key + chuẩn hoá cancel/prepay lên 1 rate (mutate copy).
     *
     * @param  array<string, mixed>  $rate
     * @param  array{checkin?: string}|null  $crawlDates
     * @return array<string, mixed>
     */
    public static function enrichRateOption(array $rate, ?array $crawlDates = null, ?string $defaultDealKey = null): array
    {
        $price = isset($rate['price']) && is_numeric($rate['price']) ? (float) $rate['price'] : null;
        $strike = isset($rate['price_strikethrough']) && is_numeric($rate['price_strikethrough'])
            ? (float) $rate['price_strikethrough']
            : null;
        $save = self::savePercent($price, $strike);
        if ($save === null && isset($rate['price_per_night'], $rate['nights'])
            && is_numeric($rate['price_per_night']) && is_numeric($rate['nights'])
            && $strike !== null) {
            // Một số dump chỉ có strike tổng — so với đêm × nights
            $approx = (float) $rate['price_per_night'] * max(1, (int) $rate['nights']);
            $save = self::savePercent($approx, $strike);
        }
        if ($save !== null) {
            $rate['save_percent'] = $save;
        }

        $cancel = is_array($rate['cancellation'] ?? null) ? $rate['cancellation'] : [];
        if (! empty($cancel['title'])) {
            $cancel['title'] = self::normalizeCancellationTitle((string) $cancel['title'], $crawlDates);
            $rate['cancellation'] = $cancel;
        }

        $prepay = is_array($rate['prepayment'] ?? null) ? $rate['prepayment'] : [];
        if (! empty($prepay['title'])) {
            $prepay['title'] = self::normalizePrepaymentTitle((string) $prepay['title']);
            $rate['prepayment'] = $prepay;
        }

        $dealKey = trim((string) ($rate['deal_key'] ?? ''));
        if ($dealKey === '') {
            $hasDeals = ! empty($rate['deals']) && is_array($rate['deals']);
            if ($hasDeals || $save !== null) {
                $rate['deal_key'] = $defaultDealKey ?: self::DEFAULT_DEAL_KEY;
            }
        }

        return $rate;
    }

    /**
     * @param  list<array<string, mixed>>  $rates
     * @param  array{checkin?: string}|null  $crawlDates
     * @return list<array<string, mixed>>
     */
    public static function enrichRateOptions(array $rates, ?array $crawlDates = null, ?string $defaultDealKey = null): array
    {
        $out = [];
        foreach ($rates as $rate) {
            if (! is_array($rate)) {
                continue;
            }
            $out[] = self::enrichRateOption($rate, $crawlDates, $defaultDealKey);
        }

        return $out;
    }

    private static function extractVietnameseDate(string $text): ?Carbon
    {
        // "24 tháng 8, 2026" / "24 tháng 8 2026"
        if (preg_match('/(\d{1,2})\s+tháng\s+(\d{1,2}),?\s*(\d{4})/iu', $text, $m)) {
            try {
                return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
        // "24/08/2026" hoặc "2026-08-24"
        if (preg_match('/\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b/', $text, $m)) {
            try {
                return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }
        if (preg_match('/\b(\d{4})-(\d{2})-(\d{2})\b/', $text, $m)) {
            try {
                return Carbon::createFromDate((int) $m[1], (int) $m[2], (int) $m[3])->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
