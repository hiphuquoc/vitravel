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
                'slug' => 'nullable|string|max:200',
                'role' => 'nullable|string|max:64',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $folderKey = $validated['folder'] ?? 'default';
        $folder = $folders[$folderKey] ?? $folders['default'];
        $variant = $validated['variant'] ?? 'card';

        $media = $mediaService->storeUploadedFile(
            $request->file('file'),
            $folder,
            null,
            $validated['slug'] ?? null,
            $validated['role'] ?? null,
        );

        return ApiResponse::success(
            $mediaService->adminMediaPayload($media, $variant),
            'Đã tải ảnh lên và tối ưu',
            201,
        );
    }

    public function uploadVideo(Request $request): JsonResponse
    {
        $mediaService = app(MediaService::class);
        $folders = $mediaService->adminFolderMap();
        $maxKb = $mediaService->effectiveVideoUploadMaxKb();

        try {
            $validated = $request->validate([
                'file' => 'required|file|mimetypes:video/mp4,video/webm,video/quicktime,video/x-m4v|max:'.$maxKb,
                'folder' => ['nullable', 'string', Rule::in(array_keys($folders))],
            ], [
                'file.mimetypes' => 'Chỉ chấp nhận video MP4, WebM hoặc MOV.',
                'file.max' => 'Video vượt quá '.round($maxKb / 1024, 1).'MB.',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $folderKey = $validated['folder'] ?? 'video_files';
        $folder = $folders[$folderKey] ?? $folders['video_files'] ?? $folders['default'];

        $media = $mediaService->storeUploadedVideo($request->file('file'), $folder);

        return ApiResponse::success(
            $mediaService->adminMediaPayload($media, 'full'),
            'Đã tải video lên',
            201,
        );
    }

    public function videoMeta(): JsonResponse
    {
        $media = app(MediaService::class);
        $maxKb = $media->effectiveVideoUploadMaxKb();

        return ApiResponse::success([
            'max_upload_kb' => $maxKb,
            'accept' => ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-m4v'],
            'hint' => 'MP4, WebM, MOV — tối đa '.($maxKb >= 1024 ? round($maxKb / 1024, 1).'MB' : $maxKb.'KB').'.',
        ]);
    }
}
