<?php

namespace App\Rules;

use App\Models\Menu;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A menu item's parent has to be one it can actually live under.
 *
 * The form narrows the dropdown, but the same checks belong here so a crafted
 * or stale request cannot create nesting the site never renders:
 *   - the parent must already sit at a depth that leaves room for a child
 *     (header 3 levels, footer 2, footer bottom bar none)
 *   - on edit, the parent cannot be the item itself or one of its descendants,
 *     which would detach the branch into a loop
 */
class ValidMenuParent implements ValidationRule
{
    public function __construct(private ?int $menuId = null)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return; // top level is always fine
        }

        $parent = Menu::with('parent.parent')->find($value);

        if (! $parent) {
            return; // the exists: rule already reports this
        }

        // A child lives in its parent's region. Saying otherwise used to be
        // accepted and then silently overwritten, so the item quietly moved to
        // a different part of the site; say so instead.
        $chosenPosition = request()->input('position');

        if ($chosenPosition && $chosenPosition !== $parent->position) {
            $labels = \App\Repositories\Menu\MenuRepository::POSITIONS;

            $fail(sprintf(
                'This parent belongs to "%s", so the item cannot be placed in "%s". Pick a parent from the same region, or leave the parent blank.',
                $labels[$parent->position] ?? $parent->position,
                $labels[$chosenPosition] ?? $chosenPosition
            ));

            return;
        }

        if (! $parent->canHaveChildren()) {
            $max = Menu::MAX_DEPTH[$parent->position] ?? 1;

            $fail($max === 1
                ? 'The :attribute cannot take child items in this region.'
                : "The :attribute is already at the deepest level this region renders ({$max}).");

            return;
        }

        if ($this->menuId && $this->isDescendantOfItem($parent)) {
            $fail('The :attribute cannot be the item itself or one of its own children.');
        }
    }

    private function isDescendantOfItem(Menu $parent): bool
    {
        $node = $parent;

        while ($node) {
            if ($node->id === $this->menuId) {
                return true;
            }
            $node = $node->parent;
        }

        return false;
    }
}
