<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\StayRateCopy;
use PHPUnit\Framework\TestCase;

class StayRateCopyTest extends TestCase
{
    public function test_save_percent_from_strike_and_price(): void
    {
        $this->assertSame(45, StayRateCopy::savePercent(550_000, 1_000_000));
        $this->assertNull(StayRateCopy::savePercent(1_000_000, 550_000));
        $this->assertNull(StayRateCopy::savePercent(0, 100));
    }

    public function test_normalize_cancellation_to_relative_days(): void
    {
        $out = StayRateCopy::normalizeCancellationTitle(
            'Hủy miễn phí trước 24 tháng 8, 2026',
            ['checkin' => '2026-08-27'],
        );
        $this->assertSame('Hủy miễn phí trước 3 ngày', $out);
    }

    public function test_normalize_prepayment_short(): void
    {
        $this->assertSame(
            'Thanh toán trước khi đến',
            StayRateCopy::normalizePrepaymentTitle('Thanh toán cho chỗ nghỉ trước khi đến'),
        );
    }

    public function test_enrich_rate_sets_save_and_default_deal(): void
    {
        $rate = StayRateCopy::enrichRateOption([
            'price' => 550_000,
            'price_strikethrough' => 1_000_000,
            'cancellation' => ['title' => 'Hủy miễn phí trước 24 tháng 8, 2026', 'refundable' => true],
            'prepayment' => ['title' => 'Thanh toán cho chỗ nghỉ trước khi đến'],
            'deals' => [['title' => 'Something from Booking']],
        ], ['checkin' => '2026-08-27']);

        $this->assertSame(45, $rate['save_percent']);
        $this->assertSame(StayRateCopy::DEFAULT_DEAL_KEY, $rate['deal_key']);
        $this->assertSame('Hủy miễn phí trước 3 ngày', $rate['cancellation']['title']);
        $this->assertSame('Thanh toán trước khi đến', $rate['prepayment']['title']);
    }

    public function test_scarcity_active_from_legacy_text(): void
    {
        $this->assertTrue(StayRateCopy::scarcityActive(['scarcity' => 'Chúng tôi còn 5 phòng']));
        $this->assertTrue(StayRateCopy::scarcityActive(['scarcity_active' => true]));
        $this->assertFalse(StayRateCopy::scarcityActive([]));
    }
}
