<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\MediaService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaLibraryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query()->orderByDesc('id');
        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                    ->orWhere('alt', 'like', "%{$search}%")
                    ->orWhere('path', 'like', "%{$search}%");
            });
        }
        $paginator = $query->paginate(min(max($request->integer('per_page', 40), 1), 100));
        $media = app(MediaService::class);

        return ApiResponse::success([
            'items' => collect($paginator->items())->map(function (Media $m) use ($media) {
                $payload = $media->adminMediaPayload($m, 'thumb') ?? [
                    'id' => $m->id,
                    'url' => null,
                    'filename' => $m->filename,
                ];

                return array_merge($payload, [
                    'created_at' => $m->created_at?->toIso8601String(),
                    'path' => $m->path ?? null,
                ]);
            }),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $row = Media::query()->findOrFail($id);
        app(MediaService::class)->deleteMedia($row);

        return ApiResponse::success(null, 'Đã xóa media');
    }
}
