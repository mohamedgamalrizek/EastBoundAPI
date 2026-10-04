<?php

namespace App\Repositories\Flight;

interface FlightInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function reissue();

    public function cancellation();

    public function refund();

    public function settle($request, $id);

    public function reports();
}
