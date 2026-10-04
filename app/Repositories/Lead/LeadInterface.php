<?php

namespace App\Repositories\Lead;

interface LeadInterface
{
    public function all();

    public function get($id);

    public function store($request);

    public function update($request, $id);

    public function delete($id);
}
