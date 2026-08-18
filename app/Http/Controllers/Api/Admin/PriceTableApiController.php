<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use App\Services\PriceTableService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PriceTableApiController extends Controller
{
    public function __construct(protected PriceTableService $prices) {}

    public function showPackage(Request $request, int $id): JsonResponse
    {
        return $this->show($request, Package::query()->with('cabinTypes.translations')->findOrFail($id));
    }

    public function updatePackage(Request $request, int $id): JsonResponse
    {
        return $this->update($request, Package::query()->with('cabinTypes.translations')->findOrFail($id));
    }

    public function quotePackage(Request $request, int $id): JsonResponse
    {
        return $this->quote($request, Package::query()->findOrFail($id));
    }

    public function showService(Request $request, int $id): JsonResponse
    {
        return $this->show($request, Service::query()->with('options.translations')->findOrFail($id));
    }

    public function updateService(Request $request, int $id): JsonResponse
    {
        return $this->update($request, Service::query()->with('options.translations')->findOrFail($id));
    }

    public function quoteService(Request $request, int $id): JsonResponse
    {
        return $this->quote($request, Service::query()->findOrFail($id));
    }

    private function show(Request $request, Model $priceable): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        return ApiResponse::success($this->prices->adminPayload($priceable, $locale));
    }

    private function update(Request $request, Model $priceable): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString() ?: 'vi';
        app()->setLocale($locale);

        try {
            $validated = $request->validate(PriceTableService::validationRules(true));
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        try {
            DB::transaction(fn () => $this->prices->sync($priceable, $validated['price_table'], $locale));
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 'INVALID_PRICE_PERIOD', 422);
        }

        $fresh = $priceable->fresh();

        return ApiResponse::success(
            $this->prices->adminPayload($fresh, $locale),
            'Đã lưu bảng giá',
        );
    }

    private function quote(Request $request, Model $priceable): JsonResponse
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'variant_id' => 'required|integer',
                'guests' => 'required|array|min:1',
                'guests.*.guest_type_id' => 'required|integer',
                'guests.*.qty' => 'required|integer|min:1|max:99',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::fromValidation($e);
        }

        $quote = $this->prices->quote(
            $priceable,
            Carbon::parse($validated['date'])->startOfDay(),
            (int) $validated['variant_id'],
            $validated['guests'],
        );

        if ($quote === null) {
            return ApiResponse::error(
                'Không có giá khớp ngày / tuỳ chọn / đối tượng khách.',
                'NO_QUOTE',
                404,
            );
        }

        return ApiResponse::success($quote);
    }
}
