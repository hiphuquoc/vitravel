<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesCoverImage
{
    protected function mediaService(): MediaService
    {
        return app(MediaService::class);
    }

    protected function coverImageRules(string $fileField = 'image'): array
    {
        $maxKb = (int) config('media.max_upload_kb', 5120);

        return [
            $fileField => 'nullable|image|max:'.$maxKb,
            'remove_image' => 'nullable|boolean',
        ];
    }

    protected function syncCoverAttachment(
        Model $model,
        Request $request,
        ?string $folder = null,
        string $fileField = 'image',
    ): void {
        $this->mediaService()->syncCoverAttachment(
            $model,
            $request,
            $fileField,
            'remove_image',
            $folder,
        );
    }

    protected function syncDirectCover(
        Model $model,
        string $column,
        Request $request,
        ?string $folder = null,
        string $fileField = 'image',
    ): void {
        $this->mediaService()->syncDirectMediaColumn(
            $model,
            $column,
            $request,
            $fileField,
            'remove_image',
            $folder,
        );
    }
}
