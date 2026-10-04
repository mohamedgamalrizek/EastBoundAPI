<?php

namespace App\Repositories\Role;

use App\Models\Role;
use App\Models\User;
use App\Enums\Status;
use App\Models\Permission;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\Role\RoleInterface;

class RoleRepository implements RoleInterface
{

    use ReturnFormatTrait;

    protected $model, $repo_permission;

    /**
     * System roles that must never be deleted. The Customer role is required
     * by public self-registration, and Super Admin is the root access role.
     */
    protected array $protectedSlugs = ['super-admin', 'customer'];

    public function __construct(Role $model, Permission $repo_permission)
    {
        $this->model = $model;
        $this->repo_permission = $repo_permission;
    }

    public function permissions()
    {
        return Permission::all();
    }

    public function all(int $paginate = null, int $status = null)
    {
        $query = $this->hideablePlatformRoles($this->model::query());

        if ($status !== null) {
            $query->where('status', $status);
        }

        $query->latest('updated_at');

        if ($paginate !== null) {
            return  $query->paginate($paginate);
        }

        return $query->get();
    }


    public function get()
    {
        return $this->hideablePlatformRoles($this->model::query())
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * The SaaS Super Admin role is seeded unconditionally so role ids never
     * shift between single-agency and SaaS installs — but in single mode it
     * owns a panel nobody can reach, so it is hidden from role listings and
     * assignment dropdowns rather than deleted.
     */
    protected function hideablePlatformRoles($query)
    {
        if (! config('saas.enabled')) {
            $query->where('slug', '!=', 'saas-super-admin');
        }

        return $query;
    }

    public function store($request)
    {
        try {

            $role             = new $this->model();
            $role->name       = $request->name;
            $role->slug       = str_replace(' ', '-', strtolower($request->name));
            $role->permissions = $request->permissions ? $request->permissions : [];
            $role->status     = $request->status;
            $role->save();

            return $this->responseWithSuccess(___('alert.successfully_added'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    public function edit($id)
    {
        return $this->model::find($id);
    }


    //role update
    public function update($request)
    {
        try {
            DB::beginTransaction();

            $role               = $this->model::findOrFail($request->id);
            $previousPermissions = $role->permissions ?? [];
            $permissions        = $request->permissions ?: [];

            $role->name         = $request->name;
            $role->permissions  = $permissions;
            $role->status       = $request->status;
            $role->slug         = str_replace(' ', '-',  strtolower($request->name));
            $role->save();

            // Users normally cache their role permissions, but the user
            // permissions screen also supports intentional per-user overrides.
            // Refresh only users that still exactly match the old role set;
            // never destroy an individual override during a role edit.
            User::where('role_id', $role->id)
                ->get(['id', 'permissions'])
                ->each(function (User $user) use ($previousPermissions, $permissions): void {
                    if (($user->permissions ?? []) === $previousPermissions) {
                        $user->permissions = $permissions;
                        $user->save();
                    }
                });

            DB::commit();

            return $this->responseWithSuccess(___('alert.successfully_updated'), []);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong'), []);
        }
    }

    //role delete
    public function delete($id)
    {
        try {
            $role  = $this->model::findOrFail($id);

            // Never delete core system roles the application depends on.
            if (in_array($role->slug, $this->protectedSlugs, true)) {
                return $this->responseWithError(___('alert.system_role_cannot_be_deleted'));
            }

            // Block deletion while the role is still assigned to users, so we
            // never orphan accounts with a dangling role_id.
            if (User::where('role_id', $role->id)->exists()) {
                return $this->responseWithError(___('alert.role_in_use_cannot_be_deleted'));
            }

            $role->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {

            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }
}
