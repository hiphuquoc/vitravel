<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use App\Models\Language;
use App\Services\NavigationMenuService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationMenuApiController extends Controller
{
    public function show(Request $request, NavigationMenuService $menu): JsonResponse
    {
        $locale = $request->string('locale', 'vi')->toString();

        return ApiResponse::success([
            'locale' => $locale,
            'languages' => Language::adminOptions(),
            'default_locale' => Language::defaultCode(),
            'is_customized' => $menu->isCustomized(),
            'kind_labels' => NavigationItem::kindLabels(),
            'zone_labels' => [
                NavigationItem::ZONE_MAIN => 'Thanh menu chính',
                NavigationItem::ZONE_MORE => 'Menu ⋯ (thêm)',
                NavigationItem::ZONE_CTA => 'Nút CTA header',
            ],
            'route_options' => $this->routeOptions(),
            'cluster_options' => collect(config('services_catalog.clusters', []))
                ->map(fn (array $cfg, string $code) => [
                    'value' => $code,
                    'label' => (string) ($cfg['label'] ?? $code),
                ])
                ->values()
                ->all(),
            'items' => $menu->adminPayload($locale),
        ]);
    }

    public function update(Request $request, NavigationMenuService $menu): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:12',
            'items' => 'required|array',
            'items.*.zone' => 'required|string|max:32',
            'items.*.kind' => 'required|string|max:48',
            'items.*.item_key' => 'required|string|max:64',
            'items.*.reference' => 'nullable|string|max:64',
            'items.*.sort' => 'nullable|integer|min:0|max:9999',
            'items.*.is_active' => 'nullable|boolean',
            'items.*.show_in_main_bar' => 'nullable|boolean',
            'items.*.label' => 'nullable|string|max:255',
            'items.*.lead_label' => 'nullable|string|max:255',
            'items.*.meta' => 'nullable|string|max:500',
        ]);

        $menu->saveAdminPayload($validated['items'], (string) $validated['locale']);

        return ApiResponse::success([
            'is_customized' => true,
            'items' => $menu->adminPayload((string) $validated['locale']),
        ], 'Đã lưu menu.');
    }

    public function reset(NavigationMenuService $menu): JsonResponse
    {
        $menu->resetToDefaults();

        return ApiResponse::success([
            'is_customized' => false,
        ], 'Đã khôi phục menu mặc định từ seed dự án.');
    }

    /** @return list<array{value: string, label: string}> */
    private function routeOptions(): array
    {
        return [
            ['value' => 'about', 'label' => 'Về chúng tôi'],
            ['value' => 'contact', 'label' => 'Liên hệ'],
            ['value' => 'team', 'label' => 'Đội ngũ'],
            ['value' => 'reviews', 'label' => 'Cảm nhận khách hàng'],
            ['value' => 'gallery', 'label' => 'Thư viện ảnh'],
            ['value' => 'videos', 'label' => 'Video trải nghiệm'],
            ['value' => 'guide.index', 'label' => 'Tất cả bài viết (hub blog)'],
            ['value' => 'customize', 'label' => 'Tour riêng (CTA)'],
        ];
    }
}
