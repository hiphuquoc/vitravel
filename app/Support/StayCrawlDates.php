<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Ngày check-in/out cố định cho crawl Booking — để bảng #hprt-table hiện đủ hạng phòng + rate.
 *
 * Quy ước: tháng kế tiếp tháng hiện tại, check-in = Thứ 2 đầu tiên trong tháng đó,
 * check-out = Thứ 4 cùng tuần (2 đêm). Khách mặc định: 2 người lớn, 1 phòng, 0 trẻ.
 */
final class StayCrawlDates
{
    /**
     * @return array{
     *     checkin: string,
     *     checkout: string,
     *     nights: int,
     *     group_adults: int,
     *     req_adults: int,
     *     no_rooms: int,
     *     group_children: int,
     *     req_children: int,
     *     query: array<string, string|int>
     * }
     */
    public static function forCrawl(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();
        $nextMonth = $now->startOfMonth()->addMonth();
        // Carbon: 1 = Monday … 7 = Sunday
        $checkin = $nextMonth->isMonday()
            ? $nextMonth
            : $nextMonth->next(CarbonImmutable::MONDAY);
        $checkout = $checkin->addDays(2);

        $query = [
            'checkin' => $checkin->toDateString(),
            'checkout' => $checkout->toDateString(),
            'group_adults' => 2,
            'req_adults' => 2,
            'no_rooms' => 1,
            'group_children' => 0,
            'req_children' => 0,
        ];

        return [
            'checkin' => $query['checkin'],
            'checkout' => $query['checkout'],
            'nights' => 2,
            'group_adults' => 2,
            'req_adults' => 2,
            'no_rooms' => 1,
            'group_children' => 0,
            'req_children' => 0,
            'query' => $query,
        ];
    }

    /** Gắn (hoặc ghi đè) query ngày/khách vào URL hotel Booking. */
    public static function applyToUrl(string $url, ?CarbonImmutable $now = null): string
    {
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }
        $dates = self::forCrawl($now);
        $query = [];
        if (! empty($parts['query'])) {
            parse_str((string) $parts['query'], $query);
        }
        foreach ($dates['query'] as $key => $value) {
            $query[$key] = $value;
        }
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port.$path.'?'.http_build_query($query).$fragment;
    }
}
