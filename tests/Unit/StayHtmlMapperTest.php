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
        $this->assertContains('Câu cá', $fields['highlights']);
        $this->assertSame(7.5, $fields['attrs']['review_scores']['cleanliness']);
        $this->assertSame(8.3, $fields['attrs']['review_scores']['wifi']);
        $this->assertArrayNotHasKey('total', $fields['attrs']['review_scores']);
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
        $this->assertStringContainsString('hồ bơi ngoài trời', mb_strtolower((string) $fields['summary']));
        $this->assertContains('Vòi sen', $fields['attrs']['amenity_groups']['bathroom'] ?? []);
        $this->assertContains('Lò sưởi ngoài trời', $fields['attrs']['amenity_groups']['outdoor'] ?? []);
        $this->assertContains('Bếp chung', $fields['attrs']['amenity_groups']['kitchen'] ?? []);
        $this->assertContains('Bình chữa cháy', $fields['attrs']['amenity_groups']['safety'] ?? []);
        $this->assertStringContainsString('Wi-fi', (string) ($fields['attrs']['amenity_groups']['media'][0] ?? ''));
        $this->assertStringContainsString('đỗ xe', mb_strtolower((string) ($fields['attrs']['amenity_groups']['parking'][0] ?? '')));
        $nearbyNames = array_column($fields['attrs']['nearby'] ?? [], 'name');
        $this->assertContains('Coi Nguon Museum', $nearbyNames);
        $this->assertContains('Nhà hàng Hiên - Charcoal Kitchen', $nearbyNames);
        $this->assertContains('Bãi Trường', $nearbyNames);
        $museum = collect($fields['attrs']['nearby'])->firstWhere('name', 'Coi Nguon Museum');
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
        $this->assertGreaterThanOrEqual(4, count($fields['attrs']['nearby'] ?? []));
        $this->assertGreaterThanOrEqual(3, count($fields['attrs']['amenity_groups'] ?? []));
    }
}
