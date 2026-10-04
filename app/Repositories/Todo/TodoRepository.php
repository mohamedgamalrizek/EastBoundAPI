<?php

namespace App\Repositories\Todo;

use App\Enums\TodoStatus;
use App\Models\Backend\Todo;
use App\Traits\ReturnFormatTrait;
use App\Repositories\Todo\TodoInterface;
use App\Repositories\Upload\UploadInterface;

class TodoRepository implements TodoInterface
{

    use ReturnFormatTrait;

    protected $model, $upload;

    public function __construct(Todo $model, UploadInterface $upload)
    {
        $this->model  = $model;
        $this->upload = $upload;
    }

    /**
     * Todo list with optional filters from the index page. Filters are applied
     * before pagination so paging keeps the current selection.
     */
    public function all($filters = [])
    {
        $filters = is_array($filters) ? $filters : [];

        // `upload` is eager-loaded alongside `user`: the index renders a
        // thumbnail per row, which would otherwise be a query each.
        return $this->model::with(['user', 'upload'])
            ->when($filters['title'] ?? null, fn ($q, $v) => $q->where('title', 'like', "%{$v}%"))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when(($filters['status'] ?? null) !== null && ($filters['status'] ?? '') !== '',
                fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function get($id)
    {
        return $this->model::find($id);
    }

    public function store($request)
    {
        try {
            $todo               = new $this->model;
            $todo->title        = $request->title;
            $todo->user_id      = $request->user;
            $todo->date         = $request->date;
            $todo->description  = $request->description;
            $todo->note         = $request->note;
            $todo->status       = $request->status;
            $todo->file_id    = $this->upload->uploadImage($request->todoFile, 'todo');
            $todo->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request)
    {
        try {

            $todo               = $this->model::findOrFail($request->id);
            $todo->title        = $request->title;
            $todo->user_id      = $request->user;
            $todo->date         = $request->date;
            $todo->description  = $request->description;
            $todo->note         = $request->note;
            $todo->status       = $request->status;
            $todo->file_id    = $this->upload->uploadImage($request->todoFile, 'todo', [], $todo->file_id);
            $todo->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }


    public function delete($id)
    {
        try {
            $todo  = $this->model::find($id);
            $this->upload->deleteImage($todo->file_id, 'delete');
            $todo->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function statusUpdate($id)
    {
        try {
            $todo                = $this->model::find($id);
            if ($todo->status     == TodoStatus::PENDING) :
                $todo->status    = TodoStatus::PROCESSING;
            elseif ($todo->status == TodoStatus::PROCESSING) :
                $todo->status    = TodoStatus::COMPLETED;
            endif;
            $todo->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }
}
