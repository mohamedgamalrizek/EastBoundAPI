<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Repositories\Role\RoleInterface;
use App\Repositories\User\UserInterface;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $repo, $roleRepo;

    public function __construct(UserInterface $repo, RoleInterface $roleRepo)
    {
        $this->repo     = $repo;
        $this->roleRepo = $roleRepo;
    }


    public function index(Request $request)
    {
        $users = $this->repo->all(
            paginate: 10,
            filters: $request->only(['name', 'email', 'phone'])
        );
        return view('backend.user.index', compact('users'));
    }

    public function create()
    {
        $roles = $this->roleRepo->all();
        return view('backend.user.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('user.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }


    public function edit($id)
    {
        $user         = $this->repo->get($id);
        $roles = $this->roleRepo->all(status: Status::ACTIVE->value);
        return view('backend.user.edit', compact('roles', 'user'));
    }

    public function update(UpdateUserRequest $request)
    {
        $result = $this->repo->update($request);
        if ($result['status']) {
            return redirect()->route('user.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }


    public function delete($id)
    {
        // Guard against an admin locking themselves out by deleting self.
        if ((int) $id === (int) auth()->id()) {
            return response()->json([
                'status'      => false,
                'message'     => ___('alert.cannot_delete_own_account'),
                'status_code' => 403,
            ], 403);
        }

        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }

    //user permissions
    public function permission($id)
    {
        $user        = $this->repo->get($id);
        $permissions = $this->roleRepo->permissions($user->role->slug);
        return view('backend.user.permissions', compact('user', 'permissions'));
    }

    public function permissionUpdate(Request $request)
    {
        $result = $this->repo->permissionUpdate($request);
        if ($result['status']) {
            return redirect()->route('user.index')->with('success', $result['message']);
        }
        return redirect()->back()->with('danger', $result['message']);
    }
}
