<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Task\TaskInterface;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends BaseCrudController
{
    protected string $viewPath      = 'backend.task';
    protected string $redirectRoute = 'task.index';

    protected ?array $listAnalyticsConfig = ['model' => 'Task', 'group' => 'status', 'label' => 'Tasks'];

    public function __construct(TaskInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store(StoreTaskRequest $request)
    {
        return $this->persist($this->repo->store($request));
    }

    public function update(UpdateTaskRequest $request)
    {
        return $this->persist($this->repo->update($request));
    }

    /* ---------------------------------------------------------------------
     | Read-only management pages
     * ------------------------------------------------------------------- */

    public function projects()
    {
        return view('backend.task.projects', $this->repo->projects());
    }

    public function assignments()
    {
        return view('backend.task.assignments', $this->repo->assignments());
    }

    public function deadlines()
    {
        return view('backend.task.deadlines', $this->repo->deadlines());
    }

    public function calendar()
    {
        return view('backend.task.calendar', $this->repo->calendar());
    }

    public function kanban()
    {
        return view('backend.task.kanban', $this->repo->kanban());
    }
}
