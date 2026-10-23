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
            $lead = new Lead($this->data($request));
            if ($request->filled('created_at')) {
                $lead->created_at = $request->created_at;
            }
            $lead->save();

            return $this->responseWithSuccess(___('alert.successfully_added'));
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function update($request, $id)
    {
        try {
            $lead = $this->model::findOrFail($id);
            $lead->fill($this->data($request));
            if ($request->filled('created_at')) {
                $lead->created_at = $request->created_at;
            }
            $lead->save();

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
            'dmc_website_id' => $request->dmc_website_id,
            'campaign_id' => $request->campaign_id,
            'lost_reason' => $request->stage === 'Lost' ? $request->lost_reason : null,
            'website'  => \App\Models\Dmc\Website::find($request->dmc_website_id)?->name ?? $request->website,
            'language' => $request->language,
            'country'  => $request->country,
            'campaign_source' => $request->campaign_source,
            'subject' => $request->subject,
            'destinations' => $request->destinations,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'duration' => $request->duration,
            'adults' => $request->adults,
            'children' => $request->children,
            'budget' => $request->budget,
            'comments' => $request->comments,
            'value'    => $request->value ?? 0,
            'stage'    => $request->stage,
            'owner'    => $request->owner,
            'notes'    => $request->notes,
        ];
    }
}
