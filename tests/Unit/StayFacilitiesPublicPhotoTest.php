<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\StayFacilities;
use Tests\TestCase;

class StayFacilitiesPublicPhotoTest extends TestCase
{
    public function test_blocks_booking_hotlinks_without_media_id(): void
    {
        $this->assertFalse(StayFacilities::shouldExposePublicPhoto(
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/405917167.jpg',
        ));
    }

    public function test_allows_booking_photos_when_media_was_imported(): void
    {
        $this->assertTrue(StayFacilities::shouldExposePublicPhoto(
            'https://cf.bstatic.com/xdata/images/hotel/max1024x768/405917167.jpg',
            123,
        ));
    }

    public function test_allows_internal_or_non_booking_photos(): void
    {
        $this->assertTrue(StayFacilities::shouldExposePublicPhoto('/storage/stays/demo.jpg'));
        $this->assertTrue(StayFacilities::shouldExposePublicPhoto(
            'https://storage.googleapis.com/example-bucket/stays/demo.jpg',
        ));
    }

    public function test_allows_media_id_even_without_url(): void
    {
        $this->assertTrue(StayFacilities::shouldExposePublicPhoto('', 99));
        $this->assertTrue(StayFacilities::shouldExposePublicPhoto(null, 99));
    }

    public function test_count_media_backed_photos(): void
    {
        $this->assertSame(0, StayFacilities::countMediaBackedPhotos([]));
        $this->assertSame(1, StayFacilities::countMediaBackedPhotos([
            ['url' => 'https://cf.bstatic.com/x.jpg', 'media_id' => 1],
            ['url' => 'https://cf.bstatic.com/y.jpg'],
        ]));
    }
}
