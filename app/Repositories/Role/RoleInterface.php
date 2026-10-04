<?php

namespace App\Repositories\Role;

interface RoleInterface
{

    public function permissions();

    public function all(int $paginate = null, int $status = null);

    public function get();

    public function store($request);

    public function edit($id);

    public function update($request);

    public function delete($id);
}
