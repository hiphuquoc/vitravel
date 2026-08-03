<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\HtmlCacheService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class CacheApiController extends Controller
{
    /** Tổng số file + trạng thái — dùng mở overlay trước khi xóa theo lô. */
    public function meta(HtmlCacheService $cache): JsonResponse
    {
        $total = $cache->countFiles();

        return ApiResponse::success([
            'total_files' => $total,
            'batch_size' => 80,
        ]);
    }

    /** Xóa một lần toàn bộ (giữ tương thích). */
    public function clear(HtmlCacheService $cache): JsonResponse
    {
        $count = $cache->clearAll();
        $cache->clearMenu();

        return ApiResponse::success(['cleared' => $count], "Đã xoá {$count} file cache HTML.");
    }

    /**
     * Xóa theo lô — client gọi lặp đến khi done=true rồi gọi finish.
     */
    public function clearBatch(Request $request, HtmlCacheService $cache): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:200',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            $result = $cache->clearBatch((int) ($validated['limit'] ?? 80));
        } catch (Throwable $e) {
            return ApiResponse::error($e->getMessage() ?: 'Xóa cache thất bại.', 'CLEAR_FAILED', 500);
        }

        return ApiResponse::success($result);
    }

    /** Dọn cache menu sau khi đã xóa hết file HTML. */
    public function finish(HtmlCacheService $cache): JsonResponse
    {
        $cache->clearMenu();

        return ApiResponse::success([
            'menu_cleared' => true,
        ], 'Đã xóa cache menu.');
    }
}
