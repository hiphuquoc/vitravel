<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\MediaAttachment;
use App\Models\Service;
use App\Models\ServiceOption;
use App\Services\MediaService;
use App\Services\ServicePurgeService;
use Tests\TestCase;

class ServicePurgeCollectMediaTest extends TestCase
{
    public function test_collect_media_ids_from_attachments_attrs_and_room_photos(): void
    {
        $service = new Service([
            'attrs' => [
                'gallery_media_ids' => [10, 11, '12'],
                'cover_media_id' => 20,
                'photos' => [
                    ['url' => 'https://cdn/a.jpg', 'media_id' => 30],
                    ['url' => 'https://cdn/b.jpg'],
                ],
            ],
        ]);

        $cover = new MediaAttachment(['media_id' => 40, 'role' => 'cover']);
        $gallery = new MediaAttachment(['media_id' => 41, 'role' => 'gallery']);
        $service->setRelation('mediaAttachments', collect([$cover, $gallery]));

        $room = new ServiceOption([
            'attrs' => [
                'photos' => [
                    ['media_id' => 50],
                    ['media_id' => 10], // trùng gallery — unique
                ],
            ],
        ]);
        $service->setRelation('options', collect([$room]));
        $service->setRelation('seoEntry', null);

        $purger = new ServicePurgeService($this->createMock(MediaService::class));
        $ids = $purger->collectMediaIds($service);
        sort($ids);

        $this->assertSame([10, 11, 12, 20, 30, 40, 41, 50], $ids);
    }
}
