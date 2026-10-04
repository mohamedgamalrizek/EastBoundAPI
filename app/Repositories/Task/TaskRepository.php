<?php

namespace App\Repositories\Task;

use App\Models\Task;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Repositories\Task\TaskInterface;

class TaskRepository extends BaseRepository implements TaskInterface
{
    /** Allowed option sets — mirror the tasks migration. */
    public const PRIORITIES = ['Low', 'Medium', 'High'];
    public const STATUSES   = ['Todo', 'In Progress', 'Done'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['assignedTo'];

    public function __construct(Task $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'assigned_to' => $request->assigned_to,
            'title'       => $request->title,
            'project'     => $request->project,
            'priority'    => $request->priority,
            'due_date'    => $request->due_date,
            'status'      => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('assignedTo', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhere('project', 'like', "%{$search}%");
            });
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'users'      => User::orderBy('name')->get(['id', 'name']),
            'priorities' => self::PRIORITIES,
            'statuses'   => self::STATUSES,
        ];
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function projects()
    {
        $projects = Task::selectRaw('project, count(*) as total')
            ->selectRaw("sum(case when status = 'Done' then 1 else 0 end) as done")
            ->whereNotNull('project')
            ->groupBy('project')
            ->orderBy('project')
            ->get();

        return ['projects' => $projects];
    }

    public function assignments()
    {
        return ['tasks' => Task::whereNotNull('assigned_to')->with('assignedTo')->get()->sortBy('assignedTo.name')->values()];
    }

    public function deadlines()
    {
        return ['tasks' => Task::whereNotNull('due_date')->with('assignedTo')->orderBy('due_date')->get()];
    }

    public function calendar()
    {
        return ['tasks' => Task::whereNotNull('due_date')->with('assignedTo')->orderBy('due_date')->get()];
    }

    public function kanban()
    {
        return ['tasks' => Task::with('assignedTo')->latest()->get()];
    }
}
