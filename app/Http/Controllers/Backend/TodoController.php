<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Todo\TodoInterface;
use App\Repositories\User\UserInterface;
use App\Http\Requests\Todo\StoreTodoRequest;
use App\Http\Requests\Todo\UpdateTodoRequest;


class TodoController extends Controller
{
    protected $repo, $userRepo;

    public function __construct(TodoInterface $repo, UserInterface $userRepo)
    {
        $this->repo = $repo;
        $this->userRepo = $userRepo;
    }



    public function index(Request $request)
    {
        $all_todo = $this->repo->all($request->only([
            'title', 'user_id', 'status', 'date_from', 'date_to',
        ]));
        $users = $this->assignableUsers();

        return view('backend.todo.index', compact('all_todo', 'users'));
    }


    public function create()
    {
        $users      = $this->assignableUsers();
        return view('backend.todo.create', compact('users'));
    }

    public function store(StoreTodoRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('todo.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function edit($id)
    {
        $todo          = $this->repo->get($id);
        $users         = $this->assignableUsers();
        return view('backend.todo.edit', compact('todo', 'users'));
    }



    public function update(UpdateTodoRequest $request)
    {
        $result = $this->repo->update($request);
        if ($result['status']) {
            return redirect()->route('todo.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    private function assignableUsers()
    {
        return User::where('status', Status::ACTIVE->value)
            ->whereHas('role', fn ($role) => $role->where('slug', '!=', 'customer'))
            ->with(['image', 'role'])
            ->latest('updated_at')
            ->get();
    }
}
