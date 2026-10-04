<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A navigation item on the public website.
 *
 * Depth is meaningful and drives how the header renders:
 *   - root with no children             → plain link
 *   - root whose children are links     → dropdown
 *   - root whose children have children → mega menu; the grandchild groups
 *                                         become the mega columns
 *
 * `position` picks the region: header, footer (column headings + links) or
 * footer_legal (the flat row in the footer bottom bar).
 */
class Menu extends Model
{
    public const POSITION_HEADER       = 'header';
    public const POSITION_FOOTER       = 'footer';
    public const POSITION_FOOTER_LEGAL = 'footer_legal';

    /**
     * How deep each region actually renders. Anything nested deeper than this
     * is saved but never drawn, so the forms refuse to create it:
     *   header       → link / dropdown / mega menu   (3)
     *   footer       → column heading + its links    (2)
     *   footer_legal → one flat row                  (1)
     */
    public const MAX_DEPTH = [
        self::POSITION_HEADER       => 3,
        self::POSITION_FOOTER       => 2,
        self::POSITION_FOOTER_LEGAL => 1,
    ];

    /** 1 for a top-level item, 2 for its child, 3 for a grandchild. */
    public function depth(): int
    {
        $depth = 1;
        $node  = $this;

        while ($node->parent_id && $node = $node->parent) {
            $depth++;
        }

        return $depth;
    }

    /** Can this item take children without exceeding its region's depth? */
    public function canHaveChildren(): bool
    {
        return $this->depth() < (self::MAX_DEPTH[$this->position] ?? 1);
    }

    protected $fillable = [
        'parent_id', 'title', 'url', 'icon', 'target', 'position', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Build one region's tree, eager-loading two levels so rendering the
     * header costs three queries rather than one per item.
     */
    public static function tree(string $position)
    {
        return static::active()->roots()->ordered()
            ->where('position', $position)
            ->with([
                'children'          => fn ($q) => $q->active(),
                'children.children' => fn ($q) => $q->active(),
            ])
            ->get();
    }

    /** Resolve the stored value to something usable in an href. */
    public function href(): string
    {
        $url = trim((string) $this->url);

        if ($url === '') {
            return '#';
        }

        if (Str::startsWith($url, ['http://', 'https://', 'mailto:', 'tel:', '#'])) {
            return $url;
        }

        return url($url);
    }

    /** Highlight the current page in the nav. */
    public function isCurrent(): bool
    {
        $url = trim((string) $this->url);

        if ($url === '' || $url === '#' || Str::startsWith($url, ['http', 'mailto:', 'tel:'])) {
            return false;
        }

        $path    = trim($url, '/');
        $current = trim(request()->path(), '/');

        return $path === $current || ($path !== '' && Str::startsWith($current, $path . '/'));
    }

    /** True when any descendant matches the current page. */
    public function hasCurrentChild(): bool
    {
        foreach ($this->children as $child) {
            if ($child->isCurrent() || $child->hasCurrentChild()) {
                return true;
            }
        }

        return false;
    }

    /** A mega menu is a root whose children are themselves groups. */
    public function isMega(): bool
    {
        return $this->children->contains(fn ($child) => $child->children->isNotEmpty());
    }
}
