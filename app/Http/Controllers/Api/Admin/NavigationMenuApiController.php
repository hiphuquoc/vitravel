<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\NavigationItem;
use App\Services\NavigationMenuService;
use App\Services\ViewDataService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NavigationMenuApiController extends Controller
{
    public function show(Request $request, NavigationMenuService $menu, ViewDataService $views): JsonResponse
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
            'hub_options' => $menu->hubOptions(),
            'category_catalog' => $this->categoryCatalog($views, $locale),
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
            'items.*.category_slugs' => 'nullable|array',
            'items.*.category_slugs.*' => 'string|max:128',
            'items.*.config' => 'nullable|array',
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

    /** @return array<string, list<array{slug: string, label: string, count: int, meta?: string}>> */
    private function categoryCatalog(ViewDataService $views, string $locale): array
    {
        app()->setLocale($locale);

        $catalog = [
            'tours' => array_map(
                static fn (array $row): array => [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'label' => (string) ($row['name'] ?? ''),
                    'count' => (int) ($row['tourCount'] ?? 0),
                    'meta' => (string) ($row['tagline'] ?? ''),
                ],
                $views->countries(),
            ),
            'cruise' => array_map(
                static fn (array $row): array => [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'label' => (string) ($row['name'] ?? ''),
                    'count' => (int) ($row['count'] ?? 0),
                ],
                $views->cruiseTypes(),
            ),
        ];

        foreach (config('services_catalog.clusters', []) as $code => $cfg) {
            if (! is_string($code) || $code === '') {
                continue;
            }

            $catalog['cluster:'.$code] = array_map(
                static fn (array $row): array => [
                    'slug' => (string) ($row['slug'] ?? ''),
                    'label' => (string) ($row['name'] ?? ''),
                    'count' => (int) ($row['count'] ?? 0),
                ],
                $views->serviceCategories($code),
            );
        }

        return $catalog;
    }
}
