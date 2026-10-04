<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Repositories\Role\RoleInterface;
use App\Http\Requests\role\StoreRoleRequest;
use App\Http\Requests\role\UpdateRoleRequest;

class RoleController extends Controller
{
    private $repo;

    function __construct(RoleInterface $repo)
    {
        if (!app()->runningInConsole() && !Schema::hasTable('settings') && !Schema::hasTable('users')) {
            abort(400);
        }

        $this->repo       = $repo;
    }

    public function index()
    {
        $roles = $this->repo->all();

        return view('backend.role.index', compact('roles'));
    }

    public function create()
    {
        $permissions    = $this->repo->permissions();

        return view('backend.role.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $result = $this->repo->store($request);

        if ($result['status']) {
            return redirect()->route('role.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function edit($id)
    {
        $permissions     = $this->repo->permissions();
        $role            = $this->repo->edit($id);

        return view('backend.role.edit', compact('permissions', 'role'));
    }

    public function update(UpdateRoleRequest $request)
    {
        $result = $this->repo->update($request);

        if ($result['status']) {
            return redirect()->route('role.index')->with('success', $result['message']);
        }
        return back()->with('danger', $result['message']);
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }
}
