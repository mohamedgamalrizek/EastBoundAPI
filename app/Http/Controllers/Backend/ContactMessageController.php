<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\ContactMessage\ContactMessageInterface;

class ContactMessageController extends Controller
{
    protected $repo;

    public function __construct(ContactMessageInterface $repo)
    {
        $this->repo = $repo;
    }

    public function index()
    {
        $messages = $this->repo->all();

        return view('backend.contact_message.index', compact('messages'));
    }

    public function show($id)
    {
        $message = $this->repo->markRead($id); // mark read on open

        return view('backend.contact_message.show', compact('message'));
    }

    public function delete($id)
    {
        $result = $this->repo->delete($id);

        return response()->json($result, $result['status_code']);
    }
}
