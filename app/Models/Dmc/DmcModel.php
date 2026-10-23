<?php

namespace App\Models\Dmc;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

abstract class DmcModel extends Model
{
    use LogsActivity;

    protected $guarded = ['id'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('DMC')
            ->logOnly(['name', 'status', 'stage', 'title', 'amount', 'total_price', 'total_cost', 'margin', 'markup',
                'approved_by', 'approval_level', 'minimum_margin', 'maximum_markup', 'team_leader_limit',
                'campaign_id', 'website_id', 'reference', 'spent_on', 'customer_id', 'lead_id', 'supplier_id', 'manager_id',
                'confirmation', 'hotel_id', 'driver_id', 'guide_id', 'activity_id', 'service_date', 'quantity',
                'unit_cost', 'unit_price', 'slug', 'domain', 'active', 'language', 'variance_approved_by', 'variance_reason'])
            ->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}
