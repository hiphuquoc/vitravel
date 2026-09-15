<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\StayCatalog\StayCompleteness;
use App\Support\StayCatalog\StayFilterPolicy;
use App\Support\StayCatalog\StayFilterSidebarParser;
use App\Support\StayCatalog\StayGeoParser;
use App\Support\StayCatalog\StayIdentity;
use App\Support\StayCatalog\StayText;
use Tests\TestCase;

class StayCatalogSupportTest extends TestCase
{
    public function test_identity_from_hotel_url(): void
    {
        $id = StayIdentity::fromUrl('https://www.booking.com/hotel/vn/la-nube-residence-phu-quoc12.vi.html?aid=1');
        $this->assertSame('booking.com', $id['source']);
        $this->assertSame('vn', $id['booking_cc']);
        $this->assertSame('vn:la-nube-residence-phu-quoc12', $id['source_hotel_key']);
        $this->assertStringContainsString('/hotel/vn/la-nube-residence-phu-quoc12', $id['canonical_url']);
        $this->assertStringNotContainsString('aid=', $id['canonical_url']);
    }

    public function test_nflt_builder_keeps_dest_and_sets_filter(): void
    {
        $url = StayIdentity::withNflt(
            'https://www.booking.com/searchresults.vi.html?ss=Cat+Ba&dest_id=-3712045&aid=1',
            'ht_id=204',
        );
        $this->assertStringContainsString('nflt=ht_id%3D204', $url);
        $this->assertStringContainsString('dest_id=-3712045', $url);
    }

    public function test_geo_parser_does_not_hardcode_phu_quoc(): void
    {
        $catBa = StayGeoParser::parseAddress('Khu 4, Thị trấn Cát Bà, Hải Phòng, Việt Nam');
        $this->assertSame('VN', $catBa['country']);
        $this->assertSame('Hải Phòng', $catBa['city']);
        $this->assertSame('Thị trấn Cát Bà', $catBa['district']);
        $label = StayGeoParser::shortLabel('Khu 4, Thị trấn Cát Bà, Hải Phòng, Việt Nam', 'Hotel Foo');
        $this->assertStringContainsString('Cát Bà', (string) $label);
        $this->assertNull(StayGeoParser::shortLabel(null, ''));
        $this->assertSame('Hotel Foo', StayGeoParser::shortLabel(null, 'Hotel Foo'));
    }

    public function test_filter_policy_groups(): void
    {
        $this->assertSame(StayFilterPolicy::IGNORE, StayFilterPolicy::forGroup('price'));
        $this->assertSame(StayFilterPolicy::IGNORE, StayFilterPolicy::forGroup('popular'));
        $this->assertSame(StayFilterPolicy::SEO_AND_CRAWL, StayFilterPolicy::forGroup('ht_id'));
        $this->assertSame(StayFilterPolicy::CRAWL_ONLY, StayFilterPolicy::forGroup('hotelfacility'));
        $this->assertSame(StayFilterPolicy::UNKNOWN, StayFilterPolicy::forGroup('brand_new_group'));

        $hotel = StayFilterPolicy::decorate(['group' => 'ht_id', 'nflt' => 'ht_id=204', 'label' => 'Khách sạn', 'count' => 86]);
        $this->assertTrue($hotel['create_page_default']);
        $this->assertTrue($hotel['crawl_default']);

        $price = StayFilterPolicy::decorate(['group' => 'price', 'nflt' => 'price=1', 'label' => 'Giá', 'count' => 10]);
        $this->assertFalse($price['create_page_default']);
        $this->assertFalse($price['crawl_default']);

        $beach = StayFilterPolicy::decorate(['group' => 'hotelfacility', 'nflt' => 'hotelfacility=433', 'label' => 'Bãi biển riêng', 'count' => 9]);
        $this->assertTrue($beach['create_page_default']);
        $this->assertTrue($beach['crawl_default']);
    }

    public function test_sidebar_parser_uses_stable_attrs_not_hashed_class(): void
    {
        $html = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/booking-filters-sidebar.html'));
        $parsed = (new StayFilterSidebarParser)->parse(
            $html,
            'https://www.booking.com/searchresults.vi.html?ss=Phu+Quoc&dest_id=-3725901&dest_type=city',
        );
        $byGroup = [];
        foreach ($parsed['filters'] as $row) {
            $byGroup[$row['group']][] = $row;
        }
        $this->assertArrayHasKey('ht_id', $byGroup);
        $this->assertSame('seo_and_crawl', $byGroup['ht_id'][0]['policy']);
        $this->assertSame('ignore', $byGroup['price'][0]['policy']);
        $this->assertSame('unknown', $byGroup['mystery_new'][0]['policy']);
        $this->assertFalse($byGroup['mystery_new'][0]['crawl_default']);
        $this->assertStringContainsString('nflt=', $byGroup['ht_id'][0]['filter_url']);
        $this->assertSame(86, $byGroup['ht_id'][0]['count']);
        $this->assertSame('Khách sạn', $byGroup['ht_id'][0]['label']);
        $this->assertStringNotContainsString('b7ef425131', $html);
    }

    public function test_phu_quoc_dump_fixture_if_present(): void
    {
        $dump = base_path('docs/decisions/filter-sidebar-booking.txt');
        if (! is_file($dump)) {
            $this->markTestSkipped('Dump Phú Quốc chưa có.');
        }
        $html = (string) file_get_contents($dump);
        $this->assertStringContainsString('data-testid="filters-sidebar"', $html);
        $parsed = (new StayFilterSidebarParser)->parse($html, 'https://www.booking.com/searchresults.vi.html?ss=Phu+Quoc');
        $policies = [];
        foreach ($parsed['filters'] as $row) {
            $policies[$row['group']] = $row['policy'];
        }
        $this->assertSame('ignore', $policies['price'] ?? null);
        $this->assertSame('seo_and_crawl', $policies['ht_id'] ?? null);
        $this->assertContains('ht_id', $parsed['groups']);
    }

    public function test_text_fold_merges_wifi_variants(): void
    {
        $this->assertSame('wifi', StayText::fold('Wi-Fi'));
        $this->assertSame('wifi', StayText::foldAmenity('Wi-Fi'));
        $this->assertSame('wifi', StayText::foldAmenity('Wifi miễn phí'));
        $this->assertSame('wifi', StayText::foldAmenity('Free WiFi'));
        $this->assertSame(StayText::fold('Cát Cò 1'), StayText::fold('Cat Co 1'));
        $this->assertSame('catco1', StayText::fold('Cát Cò 1'));
        $this->assertNotNull(StayText::aliasPair('Wifi miễn phí'));
        $this->assertNull(StayText::aliasPair('Kiểu lục địa, Kiểu Ý, Kiểu Anh/ Ai Len, Thực đơn chay, Halal, Kosher'));
    }

    public function test_completeness_score(): void
    {
        $flags = StayCompleteness::fromAttrs([
            'lat' => 20.7,
            'lng' => 107.0,
            'property_type' => 'hotel',
            'amenities' => ['Wifi'],
            'crawl' => ['source_hotel_key' => 'vn:foo'],
        ], 'vn:foo', 0);
        $this->assertTrue($flags['identity']);
        $this->assertTrue($flags['geo']);
        $this->assertFalse($flags['rooms']);
        $this->assertGreaterThan(0, $flags['score']);
    }

    public function test_discover_does_not_enqueue_from_parser(): void
    {
        $html = (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/booking-filters-sidebar.html'));
        $parsed = (new StayFilterSidebarParser)->parse($html, 'https://www.booking.com/searchresults.vi.html?ss=Test');
        $this->assertNotEmpty($parsed['filters']);
        foreach ($parsed['filters'] as $row) {
            $this->assertArrayNotHasKey('spawned', $row);
        }
    }
}
