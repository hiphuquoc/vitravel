<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class MetaApiController extends Controller
{
    public function languages(): JsonResponse
    {
        return ApiResponse::success([
            'default_code' => Language::defaultCode(),
            'items' => Language::adminOptions(),
        ]);
    }
}
