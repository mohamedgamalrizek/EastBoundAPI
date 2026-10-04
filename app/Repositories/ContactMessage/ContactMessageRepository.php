<?php

namespace App\Repositories\ContactMessage;

use App\Models\Lead;
use App\Models\ContactMessage;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use App\Repositories\ContactMessage\ContactMessageInterface;

class ContactMessageRepository implements ContactMessageInterface
{
    use ReturnFormatTrait;

    protected $model;

    public function __construct(ContactMessage $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::orderByDesc('id')->paginate(10);
    }

    public function get($id)
    {
        return $this->model::find($id);
    }

    public function store($request)
    {
        try {
            DB::transaction(function () use ($request): void {
                $message = $this->model::create([
                    'name'    => $request->name,
                    'email'   => $request->email,
                    'phone'   => $request->phone,
                    'subject' => $request->subject,
                    'message' => $request->message,
                    'status'  => 'new',
                ]);

                Lead::create([
                    'name'     => $message->name,
                    'phone'    => $message->phone,
                    'email'    => $message->email,
                    'interest' => $message->subject ?: 'General enquiry',
                    'source'   => 'Website',
                    'stage'    => 'New',
                    'notes'    => trim("Contact message #{$message->id}\n\n{$message->message}"),
                ]);
            });

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function markRead($id)
    {
        $message = $this->model::findOrFail($id);
        if ($message && $message->status === 'new') {
            $message->status = 'read';
            $message->save();
        }
        return $message;
    }

    public function delete($id)
    {
        try {
            $this->model::findOrFail($id)->delete();

            return $this->responseWithSuccess(___('alert.successfully_deleted'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }
}
