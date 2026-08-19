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
            'nearby' => [['name' => 'Bãi cũ', 'distance' => '1 km']],
            'amenity_groups' => [
                'popular' => ['WiFi'],
                'kitchen' => ['Bếp chung'],
            ],
            'pet_policy' => 'Không thú cưng',
        ];
        $incoming = [
            'address' => 'Mới',
            'nearby' => [
                ['name' => 'Bãi Trường', 'distance' => '1 km'],
                ['name' => 'Sân bay', 'distance' => '9 km'],
            ],
            'amenity_groups' => [
                'outdoor' => ['Sân vườn'],
            ],
            'pet_policy' => '',
            'check_in' => '15:00',
        ];

        $merged = StayCrawlImporter::mergeFilled($old, $incoming);

        $this->assertSame('Mới', $merged['address']);
        $this->assertSame('Bãi Trường', $merged['nearby'][0]['name']);
        $this->assertSame(['WiFi'], $merged['amenity_groups']['popular']);
        $this->assertSame(['Bếp chung'], $merged['amenity_groups']['kitchen']);
        $this->assertSame(['Sân vườn'], $merged['amenity_groups']['outdoor']);
        $this->assertSame('Không thú cưng', $merged['pet_policy']);
        $this->assertSame('15:00', $merged['check_in']);
    }
}
