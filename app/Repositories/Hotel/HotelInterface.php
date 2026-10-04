<?php

namespace App\Repositories\Hotel;

interface HotelInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function details($id);

    public function availability();

    public function vouchers();

    public function reports();
}
