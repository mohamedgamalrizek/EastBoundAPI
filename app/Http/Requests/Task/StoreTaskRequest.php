<?php

namespace App\Http\Requests\Task;

use Illuminate\Validation\Rule;
use App\Repositories\Task\TaskRepository;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title'       => ['required', 'string', 'max:150'],
            'project'     => ['nullable', 'string', 'max:100'],
            'priority'    => ['required', Rule::in(TaskRepository::PRIORITIES)],
            'due_date'    => ['nullable', 'date'],
            'status'      => ['required', Rule::in(TaskRepository::STATUSES)],
        ];
    }
}
