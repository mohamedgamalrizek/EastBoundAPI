<?php

namespace App\Support;

class AdminMenu
{
    public static function sections(): array
    {
        $sections = [];

        foreach (config('admin-menu', []) as $section) {
            $section['items'] = self::items($section['items']);

            if ($section['items'] !== []) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    private static function items(array $items): array
    {
        $visible = [];

        foreach ($items as $item) {
            if (($item['route'] ?? '') === 'dashboard' && ! hasPermission('dmc_sales_approve_gm')) {
                $item['label'] = 'Department Dashboard';
            }
            if (isset($item['permission']) && ! hasPermission($item['permission'])) {
                continue;
            }

            if (isset($item['children'])) {
                $item['children'] = self::items($item['children']);

                if ($item['children'] === []) {
                    continue;
                }
            }

            if (isset($item['route']) && ! DmcModules::routeEnabled($item['route'])) {
                continue;
            }

            $item['pending'] = ! isset($item['route']) && ! isset($item['children']);
            $item['current'] = isset($item['route']) && request()->routeIs($item['active'] ?? [$item['route']])
                && (($item['query']['view'] ?? null) === request()->query('view'));
            $item['active'] = $item['current'] || collect($item['children'] ?? [])->contains('active', true);
            $visible[] = $item;
        }

        return $visible;
    }
}
