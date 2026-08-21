<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\StayFacilities;
use Tests\TestCase;

class StayFacilitiesNormalizeTest extends TestCase
{
    public function test_resolve_public_sections_from_la_limone_fixture(): void
    {
        $raw = json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/amenity-nearby-sample.json')),
            true,
        );
        $this->assertIsArray($raw);

        $sections = StayFacilities::resolvePublicSections($raw);

        $this->assertGreaterThanOrEqual(10, count($sections['amenityGroups']));
        $labels = array_column($sections['amenityGroups'], 'label');
        $this->assertContains('Tiện ích nổi bật', $labels);
        $this->assertContains('Ngoài trời', $labels);
        $this->assertContains('Hồ bơi & biển', $labels);

        $popular = collect($sections['amenityGroups'])->firstWhere('key', 'popular');
        $this->assertNotNull($popular);
        $this->assertContains('Hồ bơi ngoài trời', $popular['items']);

        $this->assertGreaterThanOrEqual(3, count($sections['nearbyGroups']));
        $landmark = collect($sections['nearbyGroups'])->firstWhere('key', 'landmark');
        $this->assertNotNull($landmark);
        $this->assertSame('Coi Nguon Museum', $landmark['items'][0]['name'] ?? null);
        $this->assertSame('1,6 km', $landmark['items'][0]['distance'] ?? null);
    }

    public function test_normalizes_keyed_amenity_groups_json_string(): void
    {
        $raw = '{"bathroom":["Vòi sen","Khăn tắm"],"kitchen":["Tủ lạnh"]}';
        $groups = StayFacilities::normalizeAmenityGroups($raw);

        $this->assertSame(['Vòi sen', 'Khăn tắm'], $groups['bathroom'] ?? []);
        $this->assertSame(['Tủ lạnh'], $groups['kitchen'] ?? []);
    }

    public function test_display_groups_from_normalized_amenities(): void
    {
        $groups = StayFacilities::normalizeAmenityGroups([
            'bathroom' => ['Vòi sen'],
            'view' => ['Nhìn ra vườn'],
        ]);
        $display = StayFacilities::displayGroups([], $groups);

        $this->assertNotSame([], $display);
        $labels = array_column($display, 'label');
        $this->assertContains('Phòng tắm', $labels);
        $this->assertContains('Hướng tầm nhìn', $labels);
    }

    public function test_normalizes_nearby_groups_and_items(): void
    {
        $groups = StayFacilities::normalizeNearbyGroups([
            'landmark' => [
                ['name' => 'Bảo tàng', 'distance' => '1,6 km'],
            ],
        ]);
        $out = StayFacilities::nearbyGroups([], $groups);

        $this->assertCount(1, $out);
        $this->assertSame('Bảo tàng', $out[0]['items'][0]['name'] ?? null);
        $this->assertSame('1,6 km', $out[0]['items'][0]['distance'] ?? null);
    }

    public function test_normalize_stay_attrs_promotes_json_suffix_keys(): void
    {
        $groups = ['popular' => ['Hồ bơi ngoài trời', 'WiFi miễn phí']];
        $nearbyGroups = [
            'landmark' => [['name' => 'Coi Nguon Museum', 'distance' => '1,6 km', 'category' => 'landmark']],
        ];

        $attrs = StayFacilities::normalizeStayAttrs([
            'amenity_groups_json' => json_encode($groups, JSON_UNESCAPED_UNICODE),
            'nearby_groups_json' => json_encode($nearbyGroups, JSON_UNESCAPED_UNICODE),
        ]);

        $this->assertSame($groups, $attrs['amenity_groups'] ?? null);
        $this->assertSame($nearbyGroups, $attrs['nearby_groups'] ?? null);
        $this->assertArrayNotHasKey('amenity_groups_json', $attrs);
        $this->assertArrayNotHasKey('nearby_groups_json', $attrs);

        $sections = StayFacilities::resolvePublicSections($attrs);
        $this->assertNotSame([], $sections['amenityGroups']);
        $this->assertNotSame([], $sections['nearbyGroups']);
    }

    public function test_overlay_richer_attrs_keeps_grouped_amenities_over_popular_only(): void
    {
        $thin = ['amenity_groups' => ['popular' => ['WiFi']]];
        $rich = json_decode(
            (string) file_get_contents(base_path('tests/Fixtures/stay-crawl/amenity-nearby-sample.json')),
            true,
        );
        $merged = StayFacilities::overlayRicherStayAttrs($thin, is_array($rich) ? $rich : []);
        $sections = StayFacilities::resolvePublicSections($merged);

        $this->assertGreaterThan(1, count($sections['amenityGroups']));
        $this->assertNotSame([], $sections['nearbyGroups']);
    }

    public function test_normalize_review_scores_to_tag_list(): void
    {
        $fromObject = StayFacilities::normalizeReviewScores([
            'staff' => 8.6,
            'wifi' => 9,
            'total' => 8.2,
        ]);
        $this->assertEquals([
            ['tag' => 'staff', 'score' => 8.6],
            ['tag' => 'wifi', 'score' => 9.0],
        ], $fromObject);

        $fromList = StayFacilities::normalizeReviewScores([
            ['tag' => 'cleanliness', 'score' => 8.8],
            ['key' => 'location', 'score' => 9.1],
        ]);
        $this->assertEquals([
            ['tag' => 'cleanliness', 'score' => 8.8],
            ['tag' => 'location', 'score' => 9.1],
        ], $fromList);

        $migrated = StayFacilities::normalizeStayAttrs([
            'nearby' => [
                ['name' => 'Bãi Trường', 'distance' => '1 km', 'category' => 'beach'],
            ],
            'review_scores' => ['staff' => 8.5],
        ]);
        $this->assertArrayNotHasKey('nearby', $migrated);
        $this->assertSame('Bãi Trường', $migrated['nearby_groups']['beach'][0]['name'] ?? null);
        $this->assertSame([['tag' => 'staff', 'score' => 8.5]], $migrated['review_scores']);
    }

    public function test_clean_scraped_bed_label_strips_booking_ui(): void
    {
        $this->assertSame(
            '1 giường đôi cực lớn',
            StayFacilities::cleanScrapedLabel('1 giường đôi cực lớn+Hiển thị giá'),
        );
    }
}
