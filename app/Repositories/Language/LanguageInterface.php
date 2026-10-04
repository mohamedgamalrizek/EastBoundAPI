<?php

namespace App\Repositories\Language;

interface LanguageInterface
{
    public function flags();

    public function all($status = null, int $paginate = null, string $orderBy = 'id', string $sortBy = 'desc');

    public function store($request);

    public function find($id);

    public function update($request);

    public function delete($id);

    public function editPhrase($id);

    public function updatePhrase($request);
}
