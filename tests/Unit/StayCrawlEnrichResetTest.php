<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\StayCrawl\StayCrawlEnricher;
use Tests\TestCase;

class StayCrawlEnrichResetTest extends TestCase
{
    public function test_normalize_from_defaults_and_accepts_stages(): void
    {
        $this->assertSame('basic', StayCrawlEnricher::normalizeFrom(null));
        $this->assertSame('basic', StayCrawlEnricher::normalizeFrom('nope'));
        $this->assertSame('gallery', StayCrawlEnricher::normalizeFrom('Gallery'));
        $this->assertSame('rooms_modals', StayCrawlEnricher::normalizeFrom('rooms_modals'));
    }

    public function test_build_enrich_reset_gallery_starts_full_enrich(): void
    {
        $built = StayCrawlEnricher::buildEnrichReset([
            'gallery' => 'done',
            'rooms' => 'done',
            'rooms_total' => 3,
        ], 'gallery');

        $this->assertSame('gallery', $built['from']);
        $this->assertSame('pending', $built['enrich']['gallery']);
        $this->assertSame('pending', $built['enrich']['rooms']);
        $this->assertNull($built['enrich']['rooms_total']);
    }

    public function test_build_enrich_reset_rooms_skips_gallery(): void
    {
        $built = StayCrawlEnricher::buildEnrichReset([
            'gallery' => 'done',
            'gallery_count' => 12,
            'rooms' => 'done',
        ], 'rooms');

        $this->assertSame('rooms', $built['from']);
        $this->assertSame('done', $built['enrich']['gallery']);
        $this->assertSame('pending', $built['enrich']['rooms']);
        $this->assertNull($built['enrich']['rooms_total']);
        $this->assertSame(12, $built['enrich']['gallery_count']);
    }

    public function test_build_enrich_reset_rooms_modals_keeps_hashes(): void
    {
        $built = StayCrawlEnricher::buildEnrichReset([
            'gallery' => 'done',
            'rooms' => 'done',
            'rooms_total' => 2,
            'rooms_next' => 2,
            'room_hashes' => ['#RD1', '#RD2'],
            'room_names' => ['A', 'B'],
            'room_ids' => ['1', '2'],
        ], 'rooms_modals');

        $this->assertSame('rooms_modals', $built['from']);
        $this->assertSame('done', $built['enrich']['gallery']);
        $this->assertSame('pending', $built['enrich']['rooms']);
        $this->assertSame(0, $built['enrich']['rooms_next']);
        $this->assertSame(2, $built['enrich']['rooms_total']);
        $this->assertSame(['#RD1', '#RD2'], $built['enrich']['room_hashes']);
    }

    public function test_build_enrich_reset_rooms_modals_falls_back_without_hashes(): void
    {
        $built = StayCrawlEnricher::buildEnrichReset([
            'gallery' => 'done',
            'rooms' => 'done',
        ], 'rooms_modals');

        $this->assertSame('rooms', $built['from']);
        $this->assertNull($built['enrich']['rooms_total']);
    }
}
