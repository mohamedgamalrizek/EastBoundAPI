<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminMenuIconsTest extends TestCase
{
    public function test_every_menu_item_has_a_distinct_locally_available_icon(): void
    {
        $root = dirname(__DIR__, 2);
        $sections = require $root.'/config/admin-menu.php';
        $stylesheet = file_get_contents($root.'/public/backend/libs/fontawesome6/css/all.min.css');
        $icons = [];
        $glyphs = [];

        foreach ($sections as $section) {
            foreach ($this->items($section['items']) as $item) {
                $this->assertArrayHasKey('icon', $item, $item['label']);
                $this->assertMatchesRegularExpression('/^fa-(solid|brands) fa-[a-z0-9-]+$/', $item['icon']);
                [$family, $icon] = explode(' ', $item['icon']);
                $this->assertSame(1, preg_match('/\.'.preg_quote($icon, '/').':before(?=[,{])[^{}]*\{content:"([^"]+)"\}/', $stylesheet, $matches), $item['label']);
                $icons[] = $item['icon'];
                $glyphs[] = $family.' '.$matches[1];
            }
        }

        $this->assertNotEmpty($icons);
        $this->assertCount(count($icons), array_unique($icons), 'Each menu item must have its own icon.');
        $this->assertCount(count($glyphs), array_unique($glyphs), 'Icon aliases must not repeat the same glyph.');
    }

    private function items(array $items): array
    {
        $flattened = [];
        foreach ($items as $item) {
            $flattened[] = $item;
            $flattened = array_merge($flattened, $this->items($item['children'] ?? []));
        }

        return $flattened;
    }
}
