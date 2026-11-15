<?php

namespace App\Support;

use Illuminate\Support\Str;

class Breadcrumbs
{
    public static function build(array $labels = [], string $title = ''): array
    {
        $name = request()->route()?->getName() ?? '';
        $trail = [];
        $root = match (true) {
            str_starts_with($name, 'cust.') => 'cust.dashboard',
            str_starts_with($name, 'staff.') => 'staff.dashboard',
            str_starts_with($name, 'saas.') => 'saas.dashboard',
            str_starts_with($name, 'agent.') && ! auth()->user()?->can_access('dashboard_read') => 'agent.dashboard',
            default => 'dashboard',
        };
        $trail[] = ['label' => 'Dashboard', 'url' => self::url($root)];
        if ($name === $root) return [['label' => 'Dashboard', 'url' => null]];

        $matches = [];
        $walk = function ($items, $parents) use (&$walk, &$matches, $name) {
            foreach ($items as $item) {
                $path = [...$parents, ['label' => $item['label'], 'url' => isset($item['route']) ? self::url($item['route'], $item['query'] ?? []) : null]];
                if (isset($item['route']) && Str::is($item['active'] ?? [$item['route']], $name)) {
                    $matches[] = ['path' => $path, 'route' => $item['route']];
                }
                $walk($item['children'] ?? [], $path);
            }
        };
        foreach (config('admin-menu', []) as $section) {
            $walk($section['items'], [['label' => $section['label'], 'url' => null]]);
        }
        if ($matches) {
            $match = collect($matches)->sortByDesc(fn ($match) => $match['route'] === $name ? PHP_INT_MAX : strlen($match['route']))->first();
            $trail = array_merge($trail, $match['path']);
            if ($match['route'] !== $name) {
                $last = end($labels);
                $current = is_string($last) ? $last : '';
                if (! $current || $current === end($match['path'])['label']) {
                    $current = match (Str::afterLast($name, '.')) {
                        'create' => 'Create', 'edit' => 'Edit', 'show' => 'Details',
                        default => $title ?: Str::headline(Str::afterLast($name, '.')),
                    };
                }
                $trail[] = ['label' => $current, 'url' => null];
            }
        } else {
            // Legacy and portal pages keep their labels and explicit route links.
            $parent = preg_replace('/\.[^.]+$/', '.index', $name);
            foreach (array_values($labels) as $index => $label) {
                if (is_array($label)) {
                    $trail[] = ['label' => $label['label'], 'url' => isset($label['route']) ? self::url($label['route'], $label['parameters'] ?? []) : null];
                } elseif (strcasecmp((string) $label, 'Dashboard') !== 0) {
                    $trail[] = ['label' => $label, 'url' => $index === count($labels) - 2 && $parent !== $name ? self::url($parent) : null];
                }
            }
            if (count($trail) === 1) $trail[] = ['label' => $title ?: Str::headline(Str::afterLast($name, '.')), 'url' => null];
        }
        $trail[array_key_last($trail)]['url'] = null;
        return $trail;
    }

    public static function url(string $name, array $parameters = []): ?string
    {
        $route = app('router')->getRoutes()->getByName($name);
        if (! $route || ! in_array('GET', $route->methods()) || ! DmcModules::routeEnabled($name)) return null;
        foreach ($route->gatherMiddleware() as $middleware) {
            if (str_starts_with($middleware, 'hasPermission:') && ! auth()->user()?->can_access(substr($middleware, 14))) return null;
        }
        // Never invent an identifier for a parent detail page.
        if (preg_match_all('/\{([^}?]+)\}/', $route->uri(), $required)) {
            foreach ($required[1] as $key) {
                if (! array_key_exists($key, $parameters)) return null;
            }
        }
        return route($name, $parameters);
    }
}
