<?php

namespace App\Repositories\Menu;

use App\Models\Menu;
use App\Repositories\BaseRepository;
use App\Repositories\Menu\MenuInterface;

class MenuRepository extends BaseRepository implements MenuInterface
{
    /** Allowed option sets — mirror the menus migration. */
    public const POSITIONS = [
        Menu::POSITION_HEADER       => 'Header navigation',
        Menu::POSITION_FOOTER       => 'Footer link columns',
        Menu::POSITION_FOOTER_LEGAL => 'Footer bottom bar',
    ];

    public const STATUSES = ['active', 'inactive'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['parent'];

    public function __construct(Menu $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            // A child always lives in its parent's region, so the position is
            // inherited rather than trusted from the form.
            'parent_id'  => $request->parent_id ?: null,
            'title'      => $request->title,
            'url'        => $request->url,
            'icon'       => $request->icon,
            'target'     => $request->target ?: null,
            'position'   => $request->parent_id
                ? Menu::find($request->parent_id)?->position ?? $request->position
                : $request->position,
            'sort_order' => $request->sort_order ?? 0,
            'status'     => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }
        if (isset($filters['position'])) {
            $query->where('position', $filters['position']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    /**
     * Admin list, returned depth-first so the hierarchy reads top-down:
     * root, its children, their children, then the next root.
     */
    public function all(array $filters = [])
    {
        $items = $this->model->newQuery()
            ->when(filled($filters['position'] ?? null), fn ($q) => $q->where('position', $filters['position']))
            ->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->where('title', 'like', "%{$filters['search']}%"))
            ->orderBy('sort_order')->orderBy('id')->get();

        return $this->flatten($items);
    }

    /** Order a flat collection into depth-first order, tagging each row's depth. */
    private function flatten($items, $parentId = null, int $depth = 0)
    {
        $ordered = collect();

        foreach ($items->where('parent_id', $parentId) as $item) {
            $item->setAttribute('tree_depth', $depth);
            $ordered->push($item);
            $ordered = $ordered->concat($this->flatten($items, $item->id, $depth + 1));
        }

        // Rows whose parent was filtered out would otherwise vanish from the list.
        if ($depth === 0) {
            $ordered = $ordered->concat($items->whereNotIn('id', $ordered->pluck('id')));
        }

        return $ordered;
    }

    /** Block deleting a group that still has items under it. */
    protected function guardDelete($model): ?string
    {
        return $model->children()->exists()
            ? 'Remove or reassign this item\'s sub-items before deleting it.'
            : null;
    }

    public function formData(): array
    {
        return [
            'positions' => self::POSITIONS,
            'statuses'  => self::STATUSES,
            // Only items that may actually take a child in their own region:
            // the header nests three deep, the footer two, the bottom bar not
            // at all. Anything deeper would be saved but never rendered.
            'parents'   => Menu::with('parent.parent')->orderBy('position')->orderBy('sort_order')->get()
                ->filter(fn (Menu $m) => $m->canHaveChildren()),
        ];
    }
}
