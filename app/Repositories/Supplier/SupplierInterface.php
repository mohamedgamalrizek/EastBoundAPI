<?php

namespace App\Repositories\Supplier;

interface SupplierInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only management pages ----
    public function airlines();

    public function hotels();

    public function transport();

    public function visa();

    public function contracts();

    public function contractFormData();

    public function findContract($id);

    public function storeContract($request);

    public function updateContract($request);

    public function deleteContract($id);

    public function storeTransaction($request);

    public function deleteTransaction($id);

    public function ledger(array $filters = []);

    public function reports();
}
