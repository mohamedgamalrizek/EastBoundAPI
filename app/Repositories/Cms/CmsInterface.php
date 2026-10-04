<?php

namespace App\Repositories\Cms;

interface CmsInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function seo();
}
