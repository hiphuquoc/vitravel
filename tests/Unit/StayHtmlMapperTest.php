<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\StayCrawl\StayHtmlMapper;
use Tests\TestCase;

class StayHtmlMapperTest extends TestCase
{
    public function test_maps_compact_booking_fixture(): void
    {
        $html = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/booking-detail.html'));
        $fields = (new StayHtmlMapper)->map(
            $html,
            'https://www.booking.com/hotel/vn/la-nube-residence-phu-quoc12.vi.html',
        );

        $this->assertSame('La Limone Resort & Residence Phu Quoc', $fields['title']);
        $this->assertSame(3, $fields['star_rating']);
        $this->assertSame(7.2, $fields['rating']);
        $this->assertSame(93, $fields['review_count']);
        $this->assertSame('resort', $fields['attrs']['property_type']);
        $this->assertStringContainsString('Dương Tơ', (string) $fields['attrs']['address']);
        $this->assertStringNotContainsString('Sau khi đặt phòng', (string) $fields['attrs']['address']);
        $this->assertEqualsWithDelta(10.1834, $fields['attrs']['lat'], 0.001);
        $this->assertContains('Hồ bơi ngoài trời', $fields['attrs']['amenities']);
        $this->assertContains('WiFi miễn phí', $fields['attrs']['amenities']);
        $this->assertContains('Câu cá', $fields['attrs']['highlight_badges'] ?? []);
        $scoresByTag = collect($fields['attrs']['review_scores'] ?? [])->keyBy('tag');
        $this->assertSame(7.5, (float) ($scoresByTag['cleanliness']['score'] ?? 0));
        $this->assertSame(8.3, (float) ($scoresByTag['wifi']['score'] ?? 0));
        $this->assertArrayNotHasKey('total', $scoresByTag->all());
        $this->assertGreaterThanOrEqual(2, count($fields['options']));
        $names = array_column($fields['options'], 'name');
        $this->assertContains('Phòng Superior Giường Đôi', $names);
        $this->assertContains('Biệt Thự Nhìn Ra Vườn', $names);
        $villa = collect($fields['options'])->firstWhere('name', 'Biệt Thự Nhìn Ra Vườn');
        $this->assertSame(5, $villa['capacity']);
        $this->assertSame('entire_villa', $villa['attrs']['unit_type']);
        $this->assertSame('14:00', $fields['attrs']['check_in']);
        $this->assertSame('12:00', $fields['attrs']['check_out']);
        $this->assertNotEmpty($fields['attrs']['photos']);
        $this->assertStringContainsString('hồ bơi ngoài trời', mb_strtolower(strip_tags((string) ($fields['content'] ?? ''))));
        $this->assertContains('Vòi sen', $fields['attrs']['amenity_groups']['bathroom'] ?? []);
        $this->assertContains('Lò sưởi ngoài trời', $fields['attrs']['amenity_groups']['outdoor'] ?? []);
        $this->assertContains('Bếp chung', $fields['attrs']['amenity_groups']['kitchen'] ?? []);
        $this->assertContains('Bình chữa cháy', $fields['attrs']['amenity_groups']['safety'] ?? []);
        $this->assertStringContainsString('Wi-fi', (string) ($fields['attrs']['amenity_groups']['media'][0] ?? ''));
        $this->assertStringContainsString('đỗ xe', mb_strtolower((string) ($fields['attrs']['amenity_groups']['parking'][0] ?? '')));
        $this->assertArrayNotHasKey('nearby', $fields['attrs']);
        $allNearbyNames = [];
        foreach ($fields['attrs']['nearby_groups'] ?? [] as $items) {
            foreach (is_array($items) ? $items : [] as $place) {
                if (is_array($place) && ! empty($place['name'])) {
                    $allNearbyNames[] = $place['name'];
                }
            }
        }
        $this->assertContains('Coi Nguon Museum', $allNearbyNames);
        $this->assertContains('Nhà hàng Hiên - Charcoal Kitchen', $allNearbyNames);
        $this->assertContains('Bãi Trường', $allNearbyNames);
        $museum = collect($fields['attrs']['nearby_groups']['landmark'] ?? [])->firstWhere('name', 'Coi Nguon Museum');
        $this->assertSame('1,6 km', $museum['distance'] ?? null);
        $this->assertSame('landmark', $museum['category'] ?? null);
        $this->assertNotEmpty($fields['attrs']['nearby_groups']['beach'] ?? []);
        $this->assertNotEmpty($fields['attrs']['nearby_groups']['transport'] ?? []);
        $this->assertNotEmpty($fields['attrs']['pet_policy'] ?? null);
    }

    public function test_maps_chrome_stay_pack_overlays(): void
    {
        $html = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/booking-detail.html'));
        $pack = json_encode([
            'photos' => [
                ['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/111111.jpg', 'alt' => 'Hồ bơi đêm'],
                ['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/111112.jpg', 'alt' => 'Sảnh'],
            ],
            'rooms' => [[
                'name' => 'Biệt Thự Nhìn Ra Vườn',
                'text' => 'Biệt Thự Nhìn Ra Vườn 45 m² 1 giường đôi lớn Tối đa 5 khách Ban công Máy sấy',
                'photos' => [
                    ['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/333333.jpg', 'alt' => 'Phòng villa'],
                ],
                'amenities' => ['Ban công', 'Máy sấy tóc', 'WiFi'],
            ]],
            'facilities_html' => '<div data-testid="facility-group-container"><h3><div><span data-testid="facility-group-icon"></span>Phòng tắm</div></h3><ul><li><span>Bồn tắm</span></li></ul></div>',
            'policies_html' => '<div id="hotelPoliciesInc"><h3>Thú cưng</h3><p>Không cho phép thú cưng trong khuôn viên.</p><h3>Hút thuốc</h3><p>Cấm hút thuốc toàn bộ chỗ nghỉ.</p></div>',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $html = str_replace('</body>', '<script type="application/json" id="vt-stay-pack">'.$pack.'</script></body>', $html);

        $fields = (new StayHtmlMapper)->map(
            $html,
            'https://www.booking.com/hotel/vn/la-nube-residence-phu-quoc12.vi.html',
        );

        $urls = array_column($fields['attrs']['photos'] ?? [], 'url');
        $this->assertContains('https://cf.bstatic.com/xdata/images/hotel/max1024x768/111111.jpg', $urls);
        $this->assertContains('Bồn tắm', $fields['attrs']['amenity_groups']['bathroom'] ?? []);
        $this->assertStringContainsString('thú cưng', mb_strtolower((string) ($fields['attrs']['pet_policy'] ?? '')));
        $this->assertStringContainsString('hút thuốc', mb_strtolower((string) ($fields['attrs']['smoking_policy'] ?? '')));
        $villa = collect($fields['options'])->firstWhere('name', 'Biệt Thự Nhìn Ra Vườn');
        $this->assertNotNull($villa);
        $this->assertSame(45, $villa['attrs']['size_sqm'] ?? null);
        $this->assertSame(5, $villa['capacity']);
        $this->assertNotEmpty($villa['attrs']['photos'] ?? $villa['photos'] ?? []);
    }

    public function test_maps_gallery_grid_ids_and_room_modal_pack(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="vi"><body>
<h2 class="pp-header__title">La Limone Resort &amp; Residence Phu Quoc</h2>
<div data-testid="gallery-modal-grid">
  <button data-testid="gallery-grid-photo-action-405917167" aria-label="Hình 1/ 80">
    <img src="https://cf.bstatic.com/xdata/images/hotel/max300/405917167.jpg" alt="Hồ bơi">
  </button>
  <button data-testid="gallery-grid-photo-action-759082854" aria-label="Hình 2/ 80"></button>
</div>
<a data-testid="rt-name-link">Phòng Superior Giường Đôi</a>
<script type="application/json" id="vt-stay-pack">
{"photos":[],"rooms":[{"name":"Phòng Superior Giường Đôi","description":"The spacious double room features air conditioning.","size_sqm":35,"bed":"1 giường đôi cực lớn","smoking":"Hút thuốc: Không hút thuốc","highlights":["Bếp riêng","Ban công"],"amenity_groups":{"Trong nhà bếp riêng của bạn:":["Tủ lạnh","Ấm đun nước điện"],"Hướng tầm nhìn:":["Nhìn ra vườn"]},"amenities":["Tủ lạnh"],"photos":[{"url":"https://cf.bstatic.com/xdata/images/hotel/square60/759082746.jpg","alt":"Hình của Phòng Superior Giường Đôi #0"}]}],"facilities_html":"","policies_html":""}
</script>
</body></html>
HTML;
        $fields = (new StayHtmlMapper)->map($html, 'https://www.booking.com/hotel/vn/test.vi.html');
        $urls = array_column($fields['attrs']['photos'] ?? [], 'url');
        $this->assertContains('https://cf.bstatic.com/xdata/images/hotel/max1024x768/405917167.jpg', $urls);
        $this->assertContains('https://cf.bstatic.com/xdata/images/hotel/max1024x768/759082854.jpg', $urls);
        $room = collect($fields['options'])->firstWhere('name', 'Phòng Superior Giường Đôi');
        $this->assertSame(35, $room['attrs']['size_sqm'] ?? null);
        $this->assertStringContainsString('giường đôi', mb_strtolower((string) ($room['attrs']['bed'] ?? '')));
        $this->assertContains('Tủ lạnh', $room['amenities']);
        $this->assertContains('Tủ lạnh', $room['attrs']['amenity_groups']['kitchen'] ?? []);
        $this->assertContains('Nhìn ra vườn', $room['attrs']['amenity_groups']['view'] ?? []);
        $this->assertStringContainsString('Không hút thuốc', (string) ($room['attrs']['smoking'] ?? ''));
        $photoUrls = array_column($room['attrs']['photos'] ?? [], 'url');
        $this->assertContains('https://cf.bstatic.com/xdata/images/hotel/max1024x768/759082746.jpg', $photoUrls);
    }

    public function test_overlay_pack_survives_truncated_html(): void
    {
        $html = '<html><body><h2 class="pp-header__title">Overlay Stay</h2></body></html>';
        $pack = [
            'photos' => [
                ['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/405917167.jpg', 'alt' => 'Hồ bơi'],
            ],
            'rooms' => [[
                'name' => 'Phòng Superior Giường Đôi',
                'description' => 'Air conditioning',
                'size_sqm' => 35,
                'amenities' => ['Ban công'],
                'photos' => [
                    ['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/759082737.jpg', 'alt' => 'Phòng'],
                ],
            ]],
        ];
        $fields = (new StayHtmlMapper)->map(
            $html,
            'https://www.booking.com/hotel/vn/test.vi.html',
            [],
            $pack,
        );
        $urls = array_column($fields['attrs']['photos'] ?? [], 'url');
        $this->assertContains('https://cf.bstatic.com/xdata/images/hotel/max1024x768/405917167.jpg', $urls);
        $room = collect($fields['options'])->firstWhere('name', 'Phòng Superior Giường Đôi');
        $this->assertSame(35, $room['attrs']['size_sqm'] ?? null);
        $this->assertNotEmpty($room['attrs']['photos'] ?? []);
    }

    public function test_rejects_booking_i18n_json_as_policy(): void
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="vi"><body>
<h2 class="pp-header__title">Test Stay</h2>
<script type="application/json">
{"Nhận {amount_with_currency} Tín dụng","language_exception_bh_gwe_sr_privacy_no_descriptor_mobile_home_1":"Nhà di động","famex_m_hp_cpv2_price_mode_free":"Miễn phí","bhqc_sr_qc_desc_tooltip_affiliate":"Chỗ nghỉ này được đánh giá"}
</script>
<div id="hotelPoliciesInc">Nhận phòng Từ 15:00. Trả phòng Trước 11:00. Thú cưng: Không cho phép.</div>
</body></html>
HTML;
        $fields = (new StayHtmlMapper)->map($html, 'https://www.booking.com/hotel/vn/test.vi.html');
        $this->assertSame('15:00', $fields['attrs']['check_in'] ?? null);
        $this->assertSame('11:00', $fields['attrs']['check_out'] ?? null);
        $this->assertSame('Không cho phép', $fields['attrs']['pet_policy'] ?? null);
        foreach (['cancellation_policy', 'payment_policy', 'child_policy'] as $key) {
            $val = (string) ($fields['attrs'][$key] ?? '');
            $this->assertStringNotContainsString('amount_with_currency', $val);
            $this->assertStringNotContainsString('language_exception', $val);
            $this->assertStringNotContainsString('famex_', $val);
        }
    }

    public function test_maps_project_data_html_dump_when_present(): void
    {
        $path = base_path('data-html.txt');
        if (! is_file($path)) {
            $this->markTestSkipped('Thiếu data-html.txt ở thư mục gốc.');
        }

        $html = (string) file_get_contents($path);
        $this->assertGreaterThan(10_000, strlen($html));

        $fields = (new StayHtmlMapper)->map(
            $html,
            'https://www.booking.com/hotel/vn/la-nube-residence-phu-quoc12.vi.html',
        );

        $this->assertSame('La Limone Resort & Residence Phu Quoc', $fields['title']);
        $this->assertSame(3, $fields['star_rating']);
        $this->assertGreaterThanOrEqual(8, count($fields['attrs']['amenities'] ?? []));
        $this->assertGreaterThanOrEqual(6, count($fields['options']));
        $this->assertSame(93, $fields['review_count']);
        $this->assertNotEmpty($fields['attrs']['photos'] ?? []);
        $this->assertGreaterThanOrEqual(4, count($fields['attrs']['nearby_groups'] ?? []));
        $this->assertGreaterThanOrEqual(3, count($fields['attrs']['amenity_groups'] ?? []));
    }

    public function test_map_rooms_from_pack_keeps_photos_and_amenities(): void
    {
        $rooms = (new StayHtmlMapper)->mapRoomsFromPack([[
            'name' => 'Phòng Deluxe',
            'description' => 'Phòng rộng, máy lạnh',
            'size_sqm' => 28,
            'amenities' => ['Máy sấy tóc', 'WiFi'],
            'amenity_groups' => ['Phòng tắm' => ['Vòi sen']],
            'photos' => [['url' => 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/555.jpg', 'alt' => 'Giường']],
        ]]);

        $this->assertCount(1, $rooms);
        $this->assertSame('Phòng Deluxe', $rooms[0]['name']);
        $this->assertContains('Máy sấy tóc', $rooms[0]['amenities']);
        $this->assertNotEmpty($rooms[0]['attrs']['photos'] ?? []);
        $this->assertContains('Vòi sen', $rooms[0]['attrs']['amenity_groups']['bathroom'] ?? []);
    }

    public function test_maps_modern_policies_html_from_chrome_pack(): void
    {
        $html = '<html><body><h2 class="pp-header__title">Test Stay</h2></body></html>';
        $policiesHtml = <<<'HTML'
<div id="vt-policies">
  <div data-vt-policy><h3>Nhận phòng</h3><div>Từ 14:00 - 15:00. Khách được yêu cầu xuất trình giấy tờ tùy thân có ảnh.</div></div>
  <div data-vt-policy><h3>Trả phòng</h3><div>Từ 11:30 - 12:00</div></div>
  <div data-vt-policy><h3>Vật nuôi</h3><div>Vật nuôi không được phép.</div></div>
  <div data-vt-policy><h3>Trẻ em và giường</h3><div>Phù hợp cho tất cả trẻ em. Có giường phụ nếu yêu cầu — VND 300.000/người/đêm.</div></div>
</div>
HTML;
        $fields = (new StayHtmlMapper)->map(
            $html,
            'https://www.booking.com/hotel/vn/test.vi.html',
            [],
            ['policies_html' => $policiesHtml],
        );

        $this->assertSame('Từ 14:00 - 15:00', (string) ($fields['attrs']['check_in'] ?? ''));
        $this->assertSame('Từ 11:30 - 12:00', (string) ($fields['attrs']['check_out'] ?? ''));
        $this->assertStringContainsString('Vật nuôi không được phép', (string) ($fields['attrs']['pet_policy'] ?? ''));
        $this->assertStringContainsString('trẻ em', mb_strtolower((string) ($fields['attrs']['child_policy'] ?? '')));
        $this->assertStringContainsString('giấy tờ', mb_strtolower((string) ($fields['attrs']['id_required_policy'] ?? '')));
    }

    public function test_maps_booking_policies_section_without_gluing_or_swapping_times(): void
    {
        $policiesHtml = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/policies-section.html'));
        $fields = (new StayHtmlMapper)->map(
            '<html><body><h2 class="pp-header__title">La Limone</h2></body></html>',
            'https://www.booking.com/hotel/vn/la-limone.vi.html',
            [],
            ['policies_html' => $policiesHtml],
        );

        $this->assertSame('Từ 14:00 - 15:00', (string) ($fields['attrs']['check_in'] ?? ''));
        $this->assertSame('Từ 11:30 - 12:00', (string) ($fields['attrs']['check_out'] ?? ''));
        $this->assertStringNotContainsString('Trả phòng', (string) ($fields['attrs']['check_in'] ?? ''));
        $this->assertStringNotContainsString('14:00', (string) ($fields['attrs']['check_out'] ?? ''));

        $child = (string) ($fields['attrs']['child_policy'] ?? '');
        $this->assertStringContainsString('Từ 12 tuổi trở lên', $child);
        $this->assertStringContainsString('Có giường phụ nếu yêu cầu', $child);
        $this->assertStringNotContainsString('trở lênCó', $child);
        $this->assertStringNotContainsString('đêmGiá', $child);
        $this->assertStringNotContainsString('sẵn.Tất cả', $child);

        $this->assertStringContainsString('không được phép', mb_strtolower((string) ($fields['attrs']['pet_policy'] ?? '')));
        $this->assertStringContainsString('Visa', (string) ($fields['attrs']['payment_policy'] ?? ''));
        $this->assertStringContainsString('Tiền mặt', (string) ($fields['attrs']['payment_policy'] ?? ''));
    }

    public function test_maps_hprt_table_rate_options_and_crawl_dates(): void
    {
        $html = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/hprt-table.html'));
        $dates = [
            'checkin' => '2026-09-07',
            'checkout' => '2026-09-09',
            'nights' => 2,
        ];
        $rooms = (new StayHtmlMapper)->mapRoomsFromHprtHtml($html, $dates);

        $this->assertCount(2, $rooms);
        $superior = collect($rooms)->firstWhere('attrs.room_id', '589108801');
        $villa = collect($rooms)->firstWhere('attrs.room_id', '589108809');
        $this->assertNotNull($superior);
        $this->assertNotNull($villa);
        $this->assertSame('bk-589108801', $superior['code']);
        $this->assertSame('#RD589108801', $superior['attrs']['hash'] ?? null);
        $this->assertSame(28, $superior['attrs']['size_sqm'] ?? null);
        $this->assertCount(2, $superior['attrs']['rate_options'] ?? []);
        $blockIds = array_column($superior['attrs']['rate_options'], 'block_id');
        $this->assertContains('111AAA', $blockIds);
        $this->assertContains('111BBB', $blockIds);
        $withBreakfast = collect($superior['attrs']['rate_options'])->firstWhere('block_id', '111BBB');
        $this->assertTrue((bool) ($withBreakfast['breakfast']['included'] ?? false));
        $this->assertSame(1000000.0, (float) $superior['price_from']);
        $this->assertSame('2026-09-07', $superior['attrs']['crawl_dates']['checkin'] ?? null);
        $this->assertSame(2, $villa['attrs']['crawl_dates']['nights'] ?? null);
        $this->assertCount(2, $villa['attrs']['rate_options'] ?? []);
        $this->assertSame(2500000.0, (float) $villa['price_from']);
    }

    public function test_rooms_from_pack_prefers_room_id_code(): void
    {
        $rooms = (new StayHtmlMapper)->mapRoomsFromPack([[
            'name' => 'Phòng Superior Giường Đôi',
            'room_id' => '589108801',
            'hash' => '#RD589108801',
            'size_sqm' => 28,
            'amenities' => ['Ban công'],
        ]]);

        $this->assertSame('bk-589108801', $rooms[0]['code']);
        $this->assertSame('589108801', $rooms[0]['attrs']['room_id'] ?? null);
        $this->assertSame('#RD589108801', $rooms[0]['attrs']['hash'] ?? null);
    }
}
