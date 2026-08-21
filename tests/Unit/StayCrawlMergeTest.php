<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\StayCrawl\StayCrawlImporter;
use Tests\TestCase;

class StayCrawlMergeTest extends TestCase
{
    public function test_merge_filled_keeps_empty_incoming_and_replaces_present(): void
    {
        $old = [
            'address' => 'Cũ',
            'nearby_groups' => [
                'beach' => [['name' => 'Bãi cũ', 'distance' => '1 km']],
            ],
            'amenity_groups' => [
                'popular' => ['WiFi'],
                'kitchen' => ['Bếp chung'],
            ],
            'pet_policy' => 'Không thú cưng',
        ];
        $incoming = [
            'address' => 'Mới',
            'nearby_groups' => [
                'beach' => [
                    ['name' => 'Bãi Trường', 'distance' => '1 km'],
                    ['name' => 'Sân bay', 'distance' => '9 km'],
                ],
            ],
            'amenity_groups' => [
                'outdoor' => ['Sân vườn'],
            ],
            'pet_policy' => '',
            'check_in' => '15:00',
        ];

        $merged = StayCrawlImporter::mergeFilled($old, $incoming);

        $this->assertSame('Mới', $merged['address']);
        $this->assertSame('Bãi Trường', $merged['nearby_groups']['beach'][0]['name']);
        $this->assertSame(['WiFi'], $merged['amenity_groups']['popular']);
        $this->assertSame(['Bếp chung'], $merged['amenity_groups']['kitchen']);
        $this->assertSame(['Sân vườn'], $merged['amenity_groups']['outdoor']);
        $this->assertSame('Không thú cưng', $merged['pet_policy']);
        $this->assertSame('15:00', $merged['check_in']);
    }

    public function test_merge_filled_merges_rate_options_by_block_id(): void
    {
        $old = [
            'room_id' => '589108801',
            'rate_options' => [
                [
                    'block_id' => '111AAA',
                    'price_per_night' => 1000000,
                    'breakfast' => ['included' => false, 'label' => 'Bữa sáng phụ phí'],
                ],
            ],
            'photos' => [['url' => 'https://example.com/a.jpg']],
        ];
        $incoming = [
            'description' => 'Modal mô tả',
            'rate_options' => [
                [
                    'block_id' => '111AAA',
                    'cancellation' => ['title' => 'Không hoàn tiền'],
                ],
                [
                    'block_id' => '111BBB',
                    'price_per_night' => 1200000,
                    'breakfast' => ['included' => true],
                ],
            ],
            'photos' => [],
        ];

        $merged = StayCrawlImporter::mergeFilled($old, $incoming);

        $this->assertSame('Modal mô tả', $merged['description']);
        $this->assertSame('https://example.com/a.jpg', $merged['photos'][0]['url']);
        $this->assertCount(2, $merged['rate_options']);
        $aaa = collect($merged['rate_options'])->firstWhere('block_id', '111AAA');
        $this->assertSame(1000000, $aaa['price_per_night']);
        $this->assertSame('Không hoàn tiền', $aaa['cancellation']['title'] ?? null);
        $bbb = collect($merged['rate_options'])->firstWhere('block_id', '111BBB');
        $this->assertTrue((bool) ($bbb['breakfast']['included'] ?? false));
    }
}
