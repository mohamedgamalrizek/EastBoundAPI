<?php

namespace App\Repositories\Transport;

interface TransportInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function bus();

    public function train();

    public function launch();

    public function car();

    public function airport();

    public function reports();
}
