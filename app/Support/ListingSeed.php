<?php

declare(strict_types=1);

namespace App\Support;

/**
 * SSR seed batch cho trang listing — HTML cache giữ card đầu, Alpine chỉ eager/scroll tiếp.
 *
 * @phpstan-type ListingSeedMeta array{
 *   seedHtml: string,
 *   seedCount: int,
 *   seedCursor: ?string,
 *   seedHasMore: bool,
 *   skeletonCount: int,
 *   cardKind: string
 * }
 */
final class ListingSeed
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return ListingSeedMeta
     */
    public static function fromItems(array $items, string $kind, int $limit = 5): array
    {
        $kind = match ($kind) {
            'cruise' => 'cruise',
            'service' => 'service',
            default => 'tour',
        };
        $limit = max(1, min(20, $limit));
        $total = count($items);
        $slice = array_values(array_slice($items, 0, $limit));
        $last = $slice !== [] ? $slice[array_key_last($slice)] : null;
        $cursor = null;
        if ($last) {
            $raw = (string) ($last['id'] ?? $last['slug'] ?? '');
            $cursor = $raw !== '' ? $raw : null;
        }

        $html = $slice === []
            ? ''
            : view('partials.listing-cards', [
                'items' => $slice,
                'kind' => $kind,
                'variant' => 'wide',
                'isAppend' => false,
                'animate' => false,
            ])->render();

        return [
            'seedHtml' => $html,
            'seedCount' => $total,
            'seedCursor' => $cursor,
            'seedHasMore' => $total > count($slice),
            'skeletonCount' => $limit,
            'cardKind' => $kind,
        ];
    }

    /**
     * Seed từ kết quả servicesForListing (đã phân trang server-side).
     *
     * @param  array{items?: list<array<string, mixed>>, count?: int, has_more?: bool, next_cursor?: ?string}  $res
     * @return ListingSeedMeta
     */
    public static function fromServiceListing(array $res, int $limit = 5): array
    {
        $items = array_values($res['items'] ?? []);
        $total = (int) ($res['count'] ?? count($items));
        $hasMore = (bool) ($res['has_more'] ?? ($total > count($items)));
        $cursor = isset($res['next_cursor']) ? (string) $res['next_cursor'] : null;

        if ($cursor === '' && $items !== []) {
            $last = $items[array_key_last($items)];
            $cursor = (string) ($last['id'] ?? $last['slug'] ?? '') ?: null;
        }

        $html = $items === []
            ? ''
            : view('partials.listing-cards', [
                'items' => $items,
                'kind' => 'service',
                'variant' => 'wide',
                'isAppend' => false,
                'animate' => false,
            ])->render();

        return [
            'seedHtml' => $html,
            'seedCount' => $total,
            'seedCursor' => $cursor,
            'seedHasMore' => $hasMore,
            'skeletonCount' => max(1, min(20, $limit)),
            'cardKind' => 'service',
        ];
    }
}
