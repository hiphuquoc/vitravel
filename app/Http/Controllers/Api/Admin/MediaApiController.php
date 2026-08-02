<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MediaApiController extends Controller
{
    public function meta(): JsonResponse
    {
        $media = app(MediaService::class);

        return ApiResponse::success([
            'max_upload_kb' => $media->effectiveUploadMaxKb(),
            'accept' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            'folders' => array_keys($media->adminFolderMap()),
            'hint' => 'JPG, PNG, WebP, GIF — tự tối ưu WebP + variants (thumb/card/lg).',
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $mediaService = app(MediaService::class);
        $folders = $mediaService->adminFolderMap();
        $maxKb = $mediaService->effectiveUploadMaxKb();

        try {
            $validated = $request->validate([
                'file' => 'required|image|mimes:jpeg,jpg,png,webp,gif|max:'.$maxKb,
                'folder' => ['nullable', 'string', Rule::in(array_keys($folders))],
                'variant' => 'nullable|string|in:thumb,card,lg,full',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $folderKey = $validated['folder'] ?? 'default';
        $folder = $folders[$folderKey] ?? $folders['default'];
        $variant = $validated['variant'] ?? 'card';

        $media = $mediaService->storeUploadedFile($request->file('file'), $folder);

        return ApiResponse::success(
            $mediaService->adminMediaPayload($media, $variant),
            'Đã tải ảnh lên và tối ưu',
            201,
        );
    }
}
