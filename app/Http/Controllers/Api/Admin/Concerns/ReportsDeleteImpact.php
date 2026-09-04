<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Concerns;

use App\Services\Purge\EntityDeleteImpactService;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ReportsDeleteImpact
{
    protected function deleteImpactResponse(Request $request, Model $model): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        $payload = app(EntityDeleteImpactService::class)->forModel($model, $locale);

        return ApiResponse::success($payload);
    }
}
