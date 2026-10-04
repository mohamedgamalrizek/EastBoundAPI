<?php

namespace App\Repositories\Lead;

use App\Models\Lead;
use App\Traits\ReturnFormatTrait;
use App\Repositories\Lead\LeadInterface;

class LeadRepository implements LeadInterface
{
    use ReturnFormatTrait;

    protected $model;

    public function __construct(Lead $model)
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
            $this->model::create($this->data($request));

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request, $id)
    {
        try {
            $this->model::findOrFail($id)->update($this->data($request));

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
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

    private function data($request): array
    {
        return [
            'name'     => $request->name,
            'phone'    => $request->phone,
            'email'    => $request->email,
            'interest' => $request->interest,
            'source'   => $request->source,
            'value'    => $request->value ?? 0,
            'stage'    => $request->stage,
            'owner'    => $request->owner,
            'notes'    => $request->notes,
        ];
    }
}
