<?php

namespace App\Repositories\Blog;

interface BlogInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);
}
