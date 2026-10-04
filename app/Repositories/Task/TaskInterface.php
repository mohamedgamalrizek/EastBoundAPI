<?php

namespace App\Repositories\Task;

interface TaskInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function projects();

    public function assignments();

    public function deadlines();

    public function calendar();

    public function kanban();
}
