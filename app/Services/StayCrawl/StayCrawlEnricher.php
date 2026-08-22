<?php

declare(strict_types=1);

namespace App\Services\StayCrawl;

use App\Models\Service;
use App\Models\StayCrawlItem;
use App\Services\MediaService;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Luồng phụ sau khi đã có draft: gallery riêng, rồi từng phòng (modal).
 */
final class StayCrawlEnricher
{
    public function __construct(
        private readonly StayCrawlFetcher $fetcher,
        private readonly StayCrawlBrowser $browser,
        private readonly StayHtmlExtractor $extractor,
        private readonly StayHtmlMapper $mapper,
        private readonly StayCrawlImporter $importer,
        private readonly StayCrawlImageImporter $images,
        private readonly MediaService $media,
    ) {}

    public function markPending(StayCrawlItem $item): void
    {
        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $raw['enrich'] = self::freshEnrichState();
        $item->raw_json = $raw;
        $item->save();
    }

    /**
     * Chạy lại luồng phụ từ khúc chỉ định (giữ draft / service_id).
     *
     * - gallery: gallery → rooms_list → room modals
     * - rooms: bỏ qua gallery, rooms_list (HPRT) → room modals
     * - rooms_modals: giữ hash/danh sách đã có, chỉ scrape lại từng modal
     *
     * @return array{ok: bool, from: string, message: string}
     */
    public function resetFrom(StayCrawlItem $item, string $from): array
    {
        $from = self::normalizeFrom($from);
        if ($from === 'basic') {
            return ['ok' => false, 'from' => $from, 'message' => 'basic không dùng resetFrom — gọi full reset'];
        }
        if ($item->status !== StayCrawlItem::STATUS_IMPORTED || ! $item->service_id) {
            return [
                'ok' => false,
                'from' => $from,
                'message' => 'Chưa có draft imported — phải chạy lại từ property (basic)',
            ];
        }

        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $enrich = is_array($raw['enrich'] ?? null) ? $raw['enrich'] : [];
        $built = self::buildEnrichReset($enrich, $from);
        $raw['enrich'] = $built['enrich'];
        $item->raw_json = $raw;
        $item->error = null;
        $item->blocked_reason = null;
        $item->save();

        $labels = [
            'gallery' => 'gallery → phòng',
            'rooms' => 'rooms_list (HPRT) → modal phòng',
            'rooms_modals' => 'chỉ modal phòng (giữ hash)',
        ];

        return [
            'ok' => true,
            'from' => $built['from'],
            'message' => 'Đã xếp lại enrich từ '.$labels[$built['from']],
        ];
    }

    /**
     * @param  array<string, mixed>  $enrich
     * @return array{from: string, enrich: array<string, mixed>}
     */
    public static function buildEnrichReset(array $enrich, string $from): array
    {
        $from = self::normalizeFrom($from);
        if ($from === 'basic') {
            return ['from' => 'basic', 'enrich' => self::freshEnrichState()];
        }

        if ($from === 'rooms_modals') {
            $hashes = array_values(array_filter(array_map(
                'strval',
                is_array($enrich['room_hashes'] ?? null) ? $enrich['room_hashes'] : [],
            )));
            $total = (int) ($enrich['rooms_total'] ?? 0);
            if ($total <= 0) {
                $total = count($hashes);
            }
            if ($total > 0 && $hashes !== []) {
                return [
                    'from' => 'rooms_modals',
                    'enrich' => array_merge($enrich, [
                        'gallery' => 'done',
                        'rooms' => 'pending',
                        'rooms_next' => 0,
                        'rooms_total' => $total,
                        'rooms_ok' => 0,
                        'room_hashes' => $hashes,
                        'room_names' => is_array($enrich['room_names'] ?? null) ? $enrich['room_names'] : [],
                        'room_indexes' => is_array($enrich['room_indexes'] ?? null) ? $enrich['room_indexes'] : [],
                        'room_ids' => is_array($enrich['room_ids'] ?? null) ? $enrich['room_ids'] : [],
                    ]),
                ];
            }
            $from = 'rooms';
        }

        if ($from === 'rooms') {
            return [
                'from' => 'rooms',
                'enrich' => array_merge(self::freshEnrichState(), [
                    'gallery' => 'done',
                    'rooms' => 'pending',
                    'rooms_next' => 0,
                    'rooms_total' => null,
                    'gallery_count' => (int) ($enrich['gallery_count'] ?? 0),
                ]),
            ];
        }

        return ['from' => 'gallery', 'enrich' => self::freshEnrichState()];
    }

    /** @return list<string> */
    public static function fromStages(): array
    {
        return ['basic', 'gallery', 'rooms', 'rooms_modals'];
    }

    public static function normalizeFrom(?string $from): string
    {
        $from = is_string($from) ? strtolower(trim($from)) : 'basic';

        return in_array($from, self::fromStages(), true) ? $from : 'basic';
    }

    /** @return array<string, mixed> */
    public static function freshEnrichState(): array
    {
        return [
            'gallery' => 'pending',
            'rooms' => 'pending',
            'rooms_next' => 0,
            'rooms_total' => null,
            'gallery_count' => 0,
            'rooms_ok' => 0,
        ];
    }

    public function needsEnrich(StayCrawlItem $item): bool
    {
        if ($item->status !== StayCrawlItem::STATUS_IMPORTED || ! $item->service_id) {
            return false;
        }
        $enrich = is_array($item->raw_json['enrich'] ?? null) ? $item->raw_json['enrich'] : [];
        if ($enrich === []) {
            return false;
        }

        return ($enrich['gallery'] ?? 'done') !== 'done'
            || ($enrich['rooms'] ?? 'done') !== 'done';
    }

    /**
     * Chạy đúng 1 bước phụ (gallery | rooms_list | 1 phòng).
     *
     * @return array{phase: string, item: StayCrawlItem, message: string}
     */
    public function runNext(StayCrawlItem $item, bool $useProxy = false): array
    {
        $enrich = is_array($item->raw_json['enrich'] ?? null) ? $item->raw_json['enrich'] : [];
        if (($enrich['gallery'] ?? 'done') !== 'done') {
            return $this->runGallery($item, $useProxy);
        }
        if (($enrich['rooms'] ?? 'done') !== 'done') {
            if ($enrich['rooms_total'] === null) {
                return $this->runRoomsList($item, $useProxy);
            }

            return $this->runOneRoom($item, $useProxy);
        }

        return ['phase' => 'done', 'item' => $item, 'message' => 'Đã đủ gallery và phòng'];
    }

    /** @return array{phase: string, item: StayCrawlItem, message: string} */
    private function runGallery(StayCrawlItem $item, bool $useProxy): array
    {
        $browserPhotos = [];
        $pack = [];
        $imagesDir = null;
        try {
            $fetch = $this->fetcher->fetch($item->source_url, false, $useProxy, [
                'mode' => 'gallery',
                'skip_html' => true,
                'download_images' => true,
            ]);
            $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
            $browserPhotos = is_array($pack['photos'] ?? null) ? $pack['photos'] : [];
            $imagesDir = isset($fetch['images_dir']) ? (string) $fetch['images_dir'] : null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('StayCrawlEnricher: gallery browser failed', [
                'item_id' => $item->id,
                'url' => $item->source_url,
                'error' => $e->getMessage(),
            ]);
        }

        $httpPhotos = [];
        try {
            $galleryHtml = $this->fetcher->fetchGalleryHtml($item->source_url, $useProxy);
            if ($galleryHtml !== '') {
                $httpPhotos = $this->extractor->extractGalleryImages($galleryHtml);
            }
        } catch (\Throwable) {
            // HTTP fallback failed — use whatever browser gave us
        }

        $photos = array_slice($this->mergeGalleryPhotos($browserPhotos, $httpPhotos), 0, 45);
        $service = Service::query()->find($item->service_id);
        if (! $service) {
            $this->browser->cleanupImagesDir($imagesDir);
            throw new RuntimeException('Chưa có draft chỗ nghỉ để gắn gallery.');
        }

        try {
            $imported = $this->images->importPhotos($photos, $this->mediaSlug($service), 'gallery');
        } finally {
            $this->browser->cleanupImagesDir($imagesDir);
        }

        $mediaIds = array_values(array_filter(array_map(
            fn (array $row) => (int) ($row['media_id'] ?? 0),
            $imported,
        )));
        if ($mediaIds !== []) {
            $this->media->syncGalleryMediaIds($service, $mediaIds);
            $this->media->syncCoverMediaId($service, $mediaIds[0]);
        }

        $attrs = is_array($service->attrs) ? $service->attrs : [];
        // Chỉ giữ ảnh đã upload media — không lưu hotlink Booking chết vào attrs.photos.
        $catalog = array_values(array_filter(
            $imported,
            fn (array $row) => (int) ($row['media_id'] ?? 0) > 0,
        ));
        if ($catalog === [] && $imported !== []) {
            // Fallback: giữ source_url để audit / re-download sau (public vẫn lọc hotlink).
            $catalog = $imported;
        }
        $attrs['photos'] = array_map(function (array $row) {
            $mediaId = isset($row['media_id']) ? (int) $row['media_id'] : 0;
            $url = (string) ($row['url'] ?? '');
            $sourceUrl = (string) ($row['source_url'] ?? '');
            if ($mediaId > 0) {
                $media = \App\Models\Media::query()->find($mediaId);
                if ($media) {
                    $url = (string) ($media->url('lg') ?: $url);
                }
            }

            return array_filter([
                'url' => $url,
                'alt' => (string) ($row['alt'] ?? ''),
                'media_id' => $mediaId > 0 ? $mediaId : null,
                'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
            ], fn ($v) => $v !== null && $v !== '');
        }, $catalog);
        $attrs['gallery_media_ids'] = $mediaIds;
        $service->attrs = $attrs;
        $service->save();

        $uploadedCount = count($mediaIds);
        $totalCount = count($attrs['photos']);
        $chromeDownloaded = (int) data_get($pack, 'debug.download.downloaded', 0);

        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $raw['enrich']['gallery'] = 'done';
        $raw['enrich']['gallery_count'] = $totalCount;
        $raw['enrich']['gallery_uploaded'] = $uploadedCount;
        $raw['enrich']['gallery_browser_count'] = count($browserPhotos);
        $raw['enrich']['gallery_http_count'] = count($httpPhotos);
        $raw['enrich']['gallery_chrome_downloaded'] = $chromeDownloaded;
        $raw['stay_pack']['chrome_photos'] = count($browserPhotos);
        $raw['stay_pack']['http_gallery_photos'] = count($httpPhotos);
        $raw['stay_pack']['gallery_debug'] = $pack['debug'] ?? null;
        $item->raw_json = $raw;
        $item->save();

        if ($uploadedCount === 0 && $photos !== []) {
            \Illuminate\Support\Facades\Log::warning('StayCrawlEnricher: gallery không upload được media', [
                'item_id' => $item->id,
                'browser_count' => count($browserPhotos),
                'chrome_downloaded' => $chromeDownloaded,
                'download_failed' => (int) data_get($pack, 'debug.download.failed', 0),
                'gallery_debug' => $pack['debug'] ?? null,
            ]);
        }

        $base = $uploadedCount > 0
            ? "Gallery: {$uploadedCount} ảnh đã upload media"
                .($chromeDownloaded > 0 ? " (Chrome tải {$chromeDownloaded})" : '')
            : ($totalCount > 0
                ? "Gallery: {$totalCount} URL — chưa upload được media (Booking có thể chặn HTTP)"
                : 'Gallery: không lấy được ảnh modal — giữ ảnh hero nếu có');

        return [
            'phase' => 'gallery',
            'item' => $item->fresh(['service.seoEntry.translations']) ?? $item,
            'message' => $base.$this->networkHint($pack['debug'] ?? null, $uploadedCount > 0),
        ];
    }

    /** @return array{phase: string, item: StayCrawlItem, message: string} */
    private function runRoomsList(StayCrawlItem $item, bool $useProxy): array
    {
        $fetch = $this->fetcher->fetch($item->source_url, false, $useProxy, [
            'mode' => 'rooms_list',
            'skip_html' => true,
        ]);
        $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
        $rooms = is_array($pack['rooms'] ?? null) ? $pack['rooms'] : [];
        $max = (int) config('stay.crawl.max_rooms', 16);
        $total = min(count($rooms), $max);

        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $raw['enrich']['rooms_total'] = $total;
        $raw['enrich']['rooms_next'] = 0;
        $raw['enrich']['room_names'] = array_values(array_filter(array_map(
            fn ($row) => is_array($row) ? (string) ($row['name'] ?? '') : '',
            $rooms,
        )));
        $raw['enrich']['room_indexes'] = array_values(array_map(
            fn ($row) => (int) (is_array($row) ? ($row['index'] ?? 0) : 0),
            array_slice($rooms, 0, $total),
        ));
        $raw['enrich']['room_hashes'] = array_values(array_map(
            fn ($row) => is_array($row) ? (string) ($row['hash'] ?? '') : '',
            array_slice($rooms, 0, $total),
        ));
        $raw['enrich']['room_ids'] = array_values(array_map(
            fn ($row) => is_array($row) ? (string) ($row['room_id'] ?? '') : '',
            array_slice($rooms, 0, $total),
        ));

        $hprtImported = 0;
        $hprtHtml = trim((string) ($pack['hprt_html'] ?? ''));
        $crawlDates = is_array($pack['crawl_dates'] ?? null) ? $pack['crawl_dates'] : null;
        $service = Service::query()->find($item->service_id);
        if ($service && $hprtHtml !== '') {
            foreach ($this->mapper->mapRoomsFromHprtHtml($hprtHtml, $crawlDates) as $option) {
                $this->importer->overlayRoom($service, $option, 'vi');
                $hprtImported++;
            }
            $raw['stay_pack']['hprt_rooms'] = $hprtImported;
            if (is_array($crawlDates)) {
                $raw['stay_pack']['crawl_dates'] = $crawlDates;
            }
        }

        if ($total === 0) {
            $raw['enrich']['rooms'] = 'done';
        }
        $raw['stay_pack']['rooms_list_debug'] = $pack['debug'] ?? null;
        $item->raw_json = $raw;
        $item->save();

        $listMsg = $total > 0
            ? "Danh sách phòng: {$total} hạng".($hprtImported > 0 ? " + {$hprtImported} rate table" : '').' — mở trực tiếp qua hash (#RD…)'
            : ($hprtImported > 0
                ? "Đã import {$hprtImported} hạng từ #hprt-table — không có hash modal"
                : 'Không thấy hạng phòng trên trang — bỏ qua luồng phòng');

        return [
            'phase' => 'rooms_list',
            'item' => $item->fresh(['service.seoEntry.translations']) ?? $item,
            'message' => $listMsg.$this->networkHint($pack['debug'] ?? null, $total > 0 || $hprtImported > 0),
        ];
    }

    /** @return array{phase: string, item: StayCrawlItem, message: string} */
    private function runOneRoom(StayCrawlItem $item, bool $useProxy): array
    {
        $raw = is_array($item->raw_json) ? $item->raw_json : [];
        $index = (int) ($raw['enrich']['rooms_next'] ?? 0);
        $total = (int) ($raw['enrich']['rooms_total'] ?? 0);
        $domIndex = (int) (($raw['enrich']['room_indexes'][$index] ?? $index));
        $fallbackName = (string) (($raw['enrich']['room_names'][$index] ?? ''));
        $roomHash = (string) (($raw['enrich']['room_hashes'][$index] ?? ''));
        $roomId = (string) (($raw['enrich']['room_ids'][$index] ?? ''));
        if ($roomId === '' && preg_match('/#?RD(\d+)/i', $roomHash, $hm)) {
            $roomId = $hm[1];
        }
        $fetch = $this->fetcher->fetch($item->source_url, false, $useProxy, [
            'mode' => 'room',
            'skip_html' => true,
            'room_index' => $domIndex,
            'room_name' => $fallbackName,
            'room_hash' => $roomHash,
            'download_images' => true,
        ]);
        $pack = is_array($fetch['pack'] ?? null) ? $fetch['pack'] : [];
        $imagesDir = isset($fetch['images_dir']) ? (string) $fetch['images_dir'] : null;
                $rawRooms = is_array($pack['rooms'] ?? null) ? $pack['rooms'] : [];
        if ($rawRooms === [] && is_array($pack['room'] ?? null)) {
            $rawRooms = [$pack['room']];
        }
        $mapped = $this->mapper->mapRoomsFromPack($rawRooms);
        // Giữ local_path từ pack Chrome (mapRoomsFromPack có thể chỉ copy url/alt).
        $mapped = $this->overlayPackPhotoLocals($mapped, $rawRooms);

        // Nếu Chrome không trả màt room, fallback lấy trực tiếp từ raw_html (bảng HPRT đã cào)
        if ($mapped === [] && filled($item->raw_html)) {
            $htmlRooms = $this->mapper->mapRoomsFromHprtHtml($item->raw_html);
            foreach ($htmlRooms as $hr) {
                $hrId = (string) ($hr['attrs']['room_id'] ?? '');
                $hrCode = (string) ($hr['code'] ?? '');
                if (($roomId !== '' && ($hrId === $roomId || $hrCode === 'bk-'.$roomId)) ||
                    ($fallbackName !== '' && str_contains(mb_strtolower((string) ($hr['name'] ?? '')), mb_strtolower($fallbackName)))) {
                    $mapped = [$hr];
                    break;
                }
            }
        }
        
        $service = Service::query()->find($item->service_id);
        if (! $service) {
            $this->browser->cleanupImagesDir($imagesDir);
            throw new RuntimeException('Chưa có draft chỗ nghỉ để gắn hạng phòng.');
        }

        $ok = 0;
        try {
            foreach ($mapped as $option) {
                if ($roomId !== '') {
                    $option['code'] = 'bk-'.$roomId;
                    $option['attrs'] = is_array($option['attrs'] ?? null) ? $option['attrs'] : [];
                    $option['attrs']['room_id'] = $roomId;
                    $option['attrs']['hash'] = $roomHash !== '' ? $roomHash : '#RD'.$roomId;
                    // Modal không mang rate_options — không xoá rates đã import từ HPRT.
                    unset($option['attrs']['rate_options']);
                }
                $photos = array_slice(is_array($option['photos'] ?? null) ? $option['photos'] : [], 0, 12);
                if ($photos === [] && is_array($option['attrs']['photos'] ?? null)) {
                    $photos = $option['attrs']['photos'];
                }
                $imported = $this->images->importPhotos(
                    $photos,
                    $this->mediaSlug($service).'-room-'.($index + 1),
                    'room',
                );
                if ($imported !== []) {
                    $usable = array_values(array_filter(
                        $imported,
                        fn (array $row) => (int) ($row['media_id'] ?? 0) > 0,
                    ));
                    $rows = $usable !== [] ? $usable : $imported;
                    $option['photos'] = array_map(
                        fn (array $row) => array_filter([
                            'url' => $row['url'],
                            'alt' => $row['alt'] ?? '',
                            'media_id' => isset($row['media_id']) ? (int) $row['media_id'] : null,
                            'source_url' => $row['source_url'] ?? null,
                        ], fn ($v) => $v !== null && $v !== ''),
                        $rows,
                    );
                    $option['attrs'] = is_array($option['attrs'] ?? null) ? $option['attrs'] : [];
                    $option['attrs']['photos'] = $option['photos'];
                }
                $optAmenities = is_array($option['amenities'] ?? null) ? $option['amenities'] : [];
                $option['attrs'] = is_array($option['attrs'] ?? null) ? $option['attrs'] : [];
                if (! empty($optAmenities)) {
                    $option['attrs']['amenities'] = $optAmenities;
                }
                $this->importer->overlayRoom($service, $option, 'vi');
                $ok++;
            }
        } finally {
            $this->browser->cleanupImagesDir($imagesDir);
        }

        $raw['enrich']['rooms_next'] = $index + 1;
        $raw['enrich']['rooms_ok'] = (int) ($raw['enrich']['rooms_ok'] ?? 0) + $ok;
        if ($index + 1 >= $total) {
            $raw['enrich']['rooms'] = 'done';
        }
        $raw['stay_pack']['chrome_rooms'] = (int) ($raw['enrich']['rooms_ok'] ?? 0);
        $raw['stay_pack']['last_room_debug'] = $pack['debug'] ?? null;
        $item->raw_json = $raw;
        $item->save();

        $name = $mapped[0]['name'] ?? ($fallbackName !== '' ? $fallbackName : ('#'.($index + 1)));
        $photoCount = count($mapped[0]['photos'] ?? $mapped[0]['attrs']['photos'] ?? []);
        $amenityCount = is_array($mapped[0]['amenities'] ?? null) ? count($mapped[0]['amenities']) : 0;
        $hint = '';
        if ($mapped === []) {
            $modal = (bool) ($pack['debug']['room_modal'] ?? false);
            $hint = $modal ? ' — modal mở nhưng chưa đọc được nội dung' : ' — chưa mở được overlay phòng';
        }

        return [
            'phase' => 'room',
            'item' => $item->fresh(['service.seoEntry.translations']) ?? $item,
            'message' => sprintf(
                'Phòng %d/%d: %s — %d ảnh, %s tiện ích%s',
                $index + 1,
                max(1, $total),
                $name,
                $photoCount,
                $amenityCount,
                $hint,
            ).$this->networkHint($pack['debug'] ?? null, $mapped !== []),
        ];
    }

    /** @param  array<string, mixed>|null  $debug */
    private function networkHint(?array $debug, bool $ok = false): string
    {
        if ($ok) {
            return '';
        }
        $hint = (string) data_get($debug, 'network.hint', '');
        if ($hint === '' || $hint === 'ok' || $hint === 'api_ok_wait_dom') {
            return '';
        }
        $map = [
            'proxy_or_ip' => 'Booking chặn GraphQL (403/429) — cần proxy residential hoặc IP khác',
            'fingerprint_or_lazy' => 'API lazy không chạy — fingerprint Chrome/Puppeteer, chưa tới lúc cần proxy',
        ];

        return ' ['.$hint.': '.($map[$hint] ?? $hint).']';
    }

    private function mediaSlug(Service $service): string
    {
        return Str::slug((string) ($service->code ?: 'stay')) ?: 'stay';
    }

    /**
     * Merge browser-fetched photos with HTTP-fetched gallery photos, deduplicating by image ID.
     * Giữ `local_path` từ Chrome nếu có.
     *
     * @param  list<array{url?: string, alt?: string, local_path?: string}|string>  $browserPhotos
     * @param  list<array{url: string, alt: string}>  $httpPhotos
     * @return list<array{url: string, alt: string, local_path?: string}>
     */
    private function mergeGalleryPhotos(array $browserPhotos, array $httpPhotos): array
    {
        $seen = [];
        $out = [];
        $max = (int) config('stay.crawl.max_images', 120);

        $push = function (string $url, string $alt = '', string $local = '') use (&$out, &$seen, $max): void {
            if (($url === '' && $local === '') || count($out) >= $max) {
                return;
            }
            if (preg_match('#/(\d{5,})\.jpe?g#i', $url, $m)) {
                $key = $m[1];
                $url = preg_replace('#/hotel/(?:max\d+|square\d+)/#', '/hotel/max1024x768/', $url);
            } else {
                $key = $url !== '' ? $url : $local;
            }
            $hasSig = str_contains($url, '?k=') || str_contains($url, '&k=');
            if (isset($seen[$key])) {
                if ($local !== '' && empty($out[$seen[$key]]['local_path'])) {
                    $out[$seen[$key]]['local_path'] = $local;
                }
                if ($hasSig && ! str_contains((string) ($out[$seen[$key]]['url'] ?? ''), 'k=')) {
                    $out[$seen[$key]]['url'] = $url;
                }

                return;
            }
            $seen[$key] = count($out);
            $row = ['url' => $url, 'alt' => trim($alt)];
            if ($local !== '') {
                $row['local_path'] = $local;
            }
            $out[] = $row;
        };

        foreach ($browserPhotos as $photo) {
            if (is_array($photo) && (! empty($photo['url']) || ! empty($photo['local_path']))) {
                $push(
                    (string) ($photo['url'] ?? ''),
                    (string) ($photo['alt'] ?? ''),
                    (string) ($photo['local_path'] ?? ''),
                );
            } elseif (is_string($photo)) {
                $push($photo);
            }
        }

        foreach ($httpPhotos as $photo) {
            if (is_array($photo) && ! empty($photo['url'])) {
                $push((string) $photo['url'], (string) ($photo['alt'] ?? ''));
            }
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $mapped
     * @param  list<array<string, mixed>>  $packRooms
     * @return list<array<string, mixed>>
     */
    private function overlayPackPhotoLocals(array $mapped, array $packRooms): array
    {
        foreach ($mapped as $i => $option) {
            $pack = $packRooms[$i] ?? null;
            if (! is_array($pack) || ! is_array($pack['photos'] ?? null)) {
                continue;
            }
            $byUrl = [];
            foreach ($pack['photos'] as $photo) {
                if (! is_array($photo)) {
                    continue;
                }
                $url = (string) ($photo['url'] ?? '');
                $local = (string) ($photo['local_path'] ?? '');
                if ($url !== '' && $local !== '') {
                    $byUrl[$url] = $local;
                    if (preg_match('#/(\d{5,})\.jpe?g#i', $url, $m)) {
                        $byUrl['https://cf.bstatic.com/xdata/images/hotel/max1024x768/'.$m[1].'.jpg'] = $local;
                    }
                }
            }
            if ($byUrl === []) {
                continue;
            }
            $photos = array_slice(is_array($option['photos'] ?? null) ? $option['photos'] : [], 0, 12);
            if ($photos === [] && is_array($option['attrs']['photos'] ?? null)) {
                $photos = $option['attrs']['photos'];
            }
            foreach ($photos as $pi => $photo) {
                if (! is_array($photo)) {
                    continue;
                }
                $url = (string) ($photo['url'] ?? '');
                $local = $url !== '' && isset($byUrl[$url]) ? $byUrl[$url] : '';
                if ($local === '' && $url !== '' && preg_match('#/(\d{5,})\.jpe?g#i', $url, $m)) {
                    $unsigned = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/'.$m[1].'.jpg';
                    $local = $byUrl[$unsigned] ?? '';
                }
                if ($local !== '') {
                    $photos[$pi]['local_path'] = $local;
                }
            }
            $mapped[$i]['photos'] = $photos;
            $mapped[$i]['attrs'] = is_array($option['attrs'] ?? null) ? $option['attrs'] : [];
            $mapped[$i]['attrs']['photos'] = $photos;
        }

        return $mapped;
    }
}
