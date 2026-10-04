<?php

namespace App\Repositories\Todo;

interface TodoInterface
{
    public function all($filters = []);

    public function get($id);

    public function store($request);

    public function update($request);

    public function delete($id);

    public function statusUpdate($id);
}
