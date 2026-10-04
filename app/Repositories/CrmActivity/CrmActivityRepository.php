<?php

namespace App\Repositories\CrmActivity;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\CrmActivity;
use App\Repositories\BaseRepository;
use App\Repositories\CrmActivity\CrmActivityInterface;

class CrmActivityRepository extends BaseRepository implements CrmActivityInterface
{
    /** Allowed option sets — mirror the crm_activities migration. */
    public const TYPES    = ['followup', 'activity', 'note', 'communication'];
    public const CHANNELS = ['Call', 'Email', 'WhatsApp', 'Meeting', 'SMS'];

    /** Relations eager-loaded on list / find. */
    protected array $with = ['customer', 'user', 'lead'];

    public function __construct(CrmActivity $model)
    {
        parent::__construct($model);
    }

    /* ---- BaseRepository hooks ------------------------------------------- */

    protected function data($request): array
    {
        return [
            'customer_id'   => $request->customer_id,
            'lead_id'       => $request->lead_id,
            'customer_name' => $this->contactName($request),
            'type'          => $request->type,
            'subject'       => $request->subject,
            'body'          => $request->body,
            'activity_date' => $request->activity_date,
            'channel'       => $request->channel,
        ];
    }

    private function contactName($request): string
    {
        if ($request->customer_id) {
            return (string) Customer::whereKey($request->customer_id)->value('name');
        }

        if ($request->lead_id) {
            return (string) Lead::whereKey($request->lead_id)->value('name');
        }

        return (string) $request->customer_name;
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }
    }

    public function formData(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'leads'     => Lead::latest()->get(['id', 'name']),
            'types'     => self::TYPES,
            'channels'  => self::CHANNELS,
        ];
    }
}
