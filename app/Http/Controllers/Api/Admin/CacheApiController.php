<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\HtmlCacheService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CacheApiController extends Controller
{
    public function clear(HtmlCacheService $cache): JsonResponse
    {
        $count = $cache->clearAll();
        $cache->clearMenu();

        return ApiResponse::success(['cleared' => $count], "Đã xoá {$count} file cache HTML.");
    }
}
