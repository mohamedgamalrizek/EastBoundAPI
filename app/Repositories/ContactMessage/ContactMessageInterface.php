<?php

namespace App\Repositories\ContactMessage;

interface ContactMessageInterface
{
    public function all();

    public function get($id);

    public function store($request);

    public function markRead($id);

    public function delete($id);
}
