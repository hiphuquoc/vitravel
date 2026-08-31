<?php

namespace App\Http\Controllers;

use App\Services\ViewDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON phân trang gallery trang chi tiết (drawer lightbox — lazy fetch).
 */
class DetailGalleryController extends Controller
{
    public function __construct(protected ViewDataService $data) {}

    public function __invoke(Request $request): JsonResponse
    {
        $entity = (string) $request->input('entity', '');
        $id = (int) $request->input('id', 0);
        $offset = max(0, (int) $request->input('offset', 0));
        $defaultBatch = (int) config('stay.detail_gallery.batch_size', 24);
        $limit = max(1, min(48, (int) $request->input('limit', $defaultBatch)));

        if ($entity === '' || $id <= 0) {
            return response()->json(['error' => 'Invalid request'], 422);
        }

        $result = $this->data->detailGallerySlice($entity, $id, $offset, $limit);
        if ($result === null) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($result);
    }
}
