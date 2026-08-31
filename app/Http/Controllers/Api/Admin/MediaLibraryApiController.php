<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MediaLibraryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $mediaService = app(MediaService::class);
        $folders = $mediaService->adminFolderMap();
        $hiddenKeys = $mediaService->adminHiddenFromAllFolderKeys();

        $query = Media::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('folder')) {
            $folderKey = $request->string('folder')->toString();
            $prefix = $mediaService->resolveAdminFolderPath($folderKey);
            if (is_string($prefix) && $prefix !== '') {
                $prefix = trim(str_replace('\\', '/', $prefix), '/');
                $query->where(function ($q) use ($prefix) {
                    $q->where('path', $prefix)
                        ->orWhere('path', 'like', $prefix.'/%');
                });
            }
        } else {
            $mediaService->applyHiddenFolderExclusion($query);
        }

        if ($request->filled('kind')) {
            $kind = $request->string('kind')->toString();
            if ($kind === 'video') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'like', 'video/%')
                        ->orWhere('meta->kind', 'video');
                });
            } elseif ($kind === 'image') {
                $query->where(function ($q) {
                    $q->where(function ($inner) {
                        $inner->whereNull('mime_type')
                            ->orWhere('mime_type', 'like', 'image/%');
                    })->where(function ($inner) {
                        $inner->whereNull('meta->kind')
                            ->orWhere('meta->kind', '!=', 'video');
                    });
                });
            }
        }

        $paginator = $query->paginate(min(max($request->integer('per_page', 48), 1), 100));

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(
                fn (Media $m) => $mediaService->libraryPayload($m)
            )->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'folders' => collect($folders)->map(fn ($path, $key) => [
                'key' => $key,
                'path' => $path,
                'hidden_from_all' => in_array($key, $hiddenKeys, true),
            ])->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $row = Media::query()->findOrFail($id);

        return ApiResponse::success(app(MediaService::class)->libraryPayload($row));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'alt' => 'nullable|string|max:255',
                'credit' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $row = Media::query()->findOrFail($id);
        $row->fill([
            'alt' => array_key_exists('alt', $validated) ? ($validated['alt'] ?: null) : $row->alt,
            'credit' => array_key_exists('credit', $validated)
                ? ($validated['credit'] ?: null)
                : $row->credit,
        ]);
        $row->save();

        return ApiResponse::success(
            app(MediaService::class)->libraryPayload($row->fresh()),
            'Đã cập nhật media',
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $row = Media::query()->findOrFail($id);
        app(\App\Services\Purge\EntityPurgeService::class)->purge($row);

        return ApiResponse::success(null, 'Đã xóa media (kèm file GCS)');
    }
}
