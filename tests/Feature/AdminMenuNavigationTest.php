<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Tests\TestCase;

class AdminMenuNavigationTest extends TestCase
{
    public function test_pages_have_one_menu_entry_and_no_overlapping_active_rules(): void
    {
        $items = [];
        $walk = function (array $children) use (&$walk, &$items) {
            foreach ($children as $item) {
                if (isset($item['route'])) {
                    $items[] = $item;
                }
                $walk($item['children'] ?? []);
            }
        };
        foreach (config('admin-menu') as $section) {
            $walk($section['items']);
        }

        $routes = array_column($items, 'route');
        $this->assertCount(count($routes), array_unique($routes), 'Pages must not be repeated under different labels or filters.');
        foreach (app('router')->getRoutes() as $route) {
            if (! $route->getName()) {
                continue;
            }
            $matches = array_filter($items, fn ($item) => Str::is($item['active'] ?? [$item['route']], $route->getName()));
            $this->assertLessThanOrEqual(1, count($matches), $route->getName().' matches: '.implode(', ', array_column($matches, 'label')));
        }
    }
}
