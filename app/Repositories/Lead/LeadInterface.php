<?php

namespace App\Repositories\Lead;

interface LeadInterface
{
    public function all(array $filters = []);

    public function get($id);

    public function assignTo($id, int $userId);

    public function store($request);

    public function update($request, $id);

    public function delete($id);

    public function deleted();

    public function restore($id);

    public function forceDelete($id);
}
