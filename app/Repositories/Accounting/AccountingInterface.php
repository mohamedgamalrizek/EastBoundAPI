<?php

namespace App\Repositories\Accounting;

interface AccountingInterface
{
    // ---- CRUD (provided by BaseRepository) ----
    public function all(array $filters = []);

    public function find($id);

    public function formData();

    public function store($request);

    public function update($request);

    public function delete($id);

    // ---- Read-only reports ----
    // Every report takes the page's query filters: from / to (and account_id
    // on the ledger). An empty array means "all time".
    public function dashboard(array $filters = []);

    public function income(array $filters = []);

    public function expenses(array $filters = []);

    public function journal(array $filters = []);

    public function cashbook(array $filters = []);

    public function bankbook(array $filters = []);

    public function ledger(array $filters = []);

    public function trial(array $filters = []);

    public function pl(array $filters = []);

    public function balance(array $filters = []);

    public function refunds(array $filters = []);

    public function tax(array $filters = []);
}
