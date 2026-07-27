<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(protected MediaService $mediaService) {}

    public function upload(Request $request): JsonResponse
    {
        $maxKb = (int) config('media.max_upload_kb', 5120);

        $request->validate([
            'file' => 'required|image|max:'.$maxKb,
            'alt' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:120',
        ], [
            'file.required' => 'Vui lòng chọn file ảnh.',
            'file.image' => 'File phải là ảnh.',
            'file.max' => 'Ảnh không được vượt quá '.($maxKb / 1024).'MB.',
        ]);

        $folder = $request->input('folder', config('media.folder'));
        $media = $this->mediaService->storeUploadedFile($request->file('file'), $folder);

        if ($request->filled('alt')) {
            $media->update(['alt' => $request->input('alt')]);
        }

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $this->mediaService->publicUrl($media),
                'filename' => $media->filename,
                'disk' => $media->disk,
            ],
        ]);
    }
}
