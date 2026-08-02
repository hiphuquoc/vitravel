<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LanguageApiController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Language::query()->orderBy('sort')->orderBy('id')->get()->map(fn (Language $l) => [
            'id' => $l->id,
            'code' => $l->code,
            'name' => $l->name,
            'name_native' => $l->name_native,
            'flag' => $l->flag,
            'is_active' => $l->is_active,
            'is_default' => $l->is_default,
            'sort' => $l->sort,
        ]);

        return ApiResponse::success(['items' => $items]);
    }
}
