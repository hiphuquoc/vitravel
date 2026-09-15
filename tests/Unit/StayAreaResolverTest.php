<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\StayArea;
use App\Services\StayCatalog\StayAreaResolver;
use Tests\TestCase;

class StayAreaResolverTest extends TestCase
{
    public function test_resolves_cat_ba_from_address(): void
    {
        $catBa = new StayArea([
            'slug' => 'cat-ba',
            'level' => StayArea::LEVEL_DESTINATION,
            'aliases' => ['cat ba', 'cát bà'],
            'booking_dest_id' => '-3712045',
        ]);
        $haLong = new StayArea([
            'slug' => 'ha-long',
            'level' => StayArea::LEVEL_DESTINATION,
            'aliases' => ['ha long', 'hạ long'],
        ]);
        $pool = collect([$catBa, $haLong]);
        $resolver = new StayAreaResolver;
        $hit = $resolver->resolve('Khu 4, Thị trấn Cát Bà, Cát Hải, Hải Phòng, Việt Nam', null, null, null, 'vn', $pool);
        $this->assertNotNull($hit);
        $this->assertSame('cat-ba', $hit->slug);

        $byDest = $resolver->resolve(null, '-3712045', null, null, 'vn', $pool);
        $this->assertSame('cat-ba', $byDest?->slug);

        $hl = $resolver->resolve('Bãi Cháy, Hạ Long, Quảng Ninh, Việt Nam', null, null, null, 'vn', $pool);
        $this->assertSame('ha-long', $hl?->slug);
    }
}
