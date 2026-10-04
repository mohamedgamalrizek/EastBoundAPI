<?php

namespace App\Repositories\Campaign;

use App\Models\Campaign;
use App\Repositories\BaseRepository;
use App\Repositories\Campaign\CampaignInterface;

class CampaignRepository extends BaseRepository implements CampaignInterface
{
    public const CHANNEL = ['Email', 'SMS', 'Social', 'Google Ads', 'Print'];
    public const STATUS = ['Planned', 'Running', 'Paused', 'Completed'];

    public function __construct(Campaign $model)
    {
        parent::__construct($model);
    }

    protected function data($request): array
    {
        return [
            'name' => $request->name,
            'channel' => $request->channel,
            'audience' => $request->audience,
            'budget' => $request->budget,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status,
        ];
    }

    protected function applyFilters($query, array $filters): void
    {
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('audience', 'like', "%{$search}%");
            });
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    public function formData(): array
    {
        return [
            'channel_options' => self::CHANNEL,
            'status_options' => self::STATUS,
        ];
    }
}
