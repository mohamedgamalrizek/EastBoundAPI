<?php

namespace App\Repositories\Hajj;

interface HajjInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function hotelAllocation();

    public function flightAllocation();

    public function groups();

    public function storeGroup($request);

    public function storeFlight($request);

    public function deleteFlight($id);

    public function deleteGroup($id);

    public function payments();

    public function allocate($request, $id);

    public function documents();

    public function updateDocumentStatus($id, string $status);

    public function reports();
}
