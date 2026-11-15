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

    public function all(array $filters = [])
    {
        return $this->model::filtered($filters)->paginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function get($id)
    {
        return $this->model::find($id);
    }

    public function assignTo($id, int $userId)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $userId) {
            $lead = $this->model::lockForUpdate()->findOrFail($id);
            $user = \App\Models\User::leadAssignees()->findOrFail($userId);
            $lead->assigned_to = $user->id;
            $lead->owner = $user->name;
            $lead->save();
            return $lead;
        });
    }

    public function store($request)
    {
        try {
            $lead = new Lead($this->data($request));
            if ($request->filled('created_at')) {
                $lead->created_at = $request->created_at;
            }
            $this->assign($lead, $request, true);
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
            $this->assign($lead, $request, false);
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

    public function deleted()
    {
        return $this->model::onlyTrashed()->orderByDesc('deleted_at')->orderByDesc('id')->paginate(10);
    }

    public function restore($id)
    {
        try {
            $this->model::onlyTrashed()->findOrFail($id)->restore();
            return $this->responseWithSuccess('Lead restored successfully.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function forceDelete($id)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
                $lead = $this->model::onlyTrashed()->findOrFail($id);
                $lead->crmActivities()->delete();
                $lead->forceDelete();
            });
            return $this->responseWithSuccess('Lead permanently deleted.');
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    private function assign(Lead $lead, $request, bool $creating): void
    {
        if (auth()->user()?->can_access('lead_assign') && $request->exists('assigned_to')) {
            $lead->assigned_to = $request->input('assigned_to') ?: null;
            $lead->owner = $lead->assigned_to ? \App\Models\User::findOrFail($lead->assigned_to)->name : null;
        } elseif ($creating) {
            $lead->assigned_to = auth()->id();
            $lead->owner = auth()->user()?->name;
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

            'notes'    => $request->notes,
        ];
    }
}
