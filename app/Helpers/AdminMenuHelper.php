<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AdminMenuHelper
{
    public static function getMenuSections(): array
    {
        $config = config('menu.admin-menu-sections', []);
        $user = Auth::user();
        $sections = [];

        foreach ($config as $sectionKey => $section) {
            if (! self::userHasAnyRole($user, $section['role'] ?? [])) {
                continue;
            }

            $sections[$sectionKey] = [
                'title' => $section['title'],
            ];
        }

        return $sections;
    }

    public static function getMenuItems(string $section): array
    {
        $config = config('menu.admin-menu-sections.'.$section, []);

        if (empty($config['items'])) {
            return [];
        }

        $user = Auth::user();
        $currentRoute = request()->route()?->getName() ?? '';
        $items = [];

        foreach ($config['items'] as $item) {
            if (isset($item['role']) && ! self::userHasAnyRole($user, $item['role'])) {
                continue;
            }

            $menuItem = [
                'label' => $item['label'],
                'svg' => $item['svg'] ?? null,
                'url' => null,
                'route' => null,
                'active' => false,
                'onclick' => $item['onclick'] ?? null,
            ];

            if (isset($item['route'])) {
                $menuItem['route'] = $item['route'];
                $menuItem['url'] = route($item['route']);
                $menuItem['active'] = $currentRoute === $item['route']
                    || str_starts_with($currentRoute, str_replace('.list', '', $item['route']).'.');
            } elseif (isset($item['url'])) {
                $menuItem['url'] = $item['url'];
            }

            $items[] = $menuItem;
        }

        return $items;
    }

    /**
     * @param  list<string>  $roles
     */
    private static function userHasAnyRole($user, array $roles): bool
    {
        if (! $user) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
