<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use LogsActivity;

    public const LANGUAGES = ['English', 'Spanish', 'Italian', 'French'];

    public const CAMPAIGN_SOURCES = ['Google Ads', 'Social Media', 'Organic Leads', 'Referral', 'Other'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Lead')->logOnly(['name', 'stage', 'value', 'website', 'dmc_website_id', 'language', 'country', 'campaign_id', 'campaign_source', 'lost_reason', 'created_at'])->logOnlyDirty()->setDescriptionForEvent(fn (string $event) => $event);
    }

    protected $fillable = [
        'dmc_external_reference', 'dmc_payload_hash',
        'dmc_website_id', 'campaign_id', 'lost_reason',
        'assigned_to',
        'name',
        'phone',
        'email',
        'interest',
        'source',
        'website',
        'language',
        'country',
        'campaign_source',
        'subject',
        'destinations',
        'from_date',
        'to_date',
        'duration',
        'adults',
        'children',
        'budget',
        'comments',
        'value',
        'stage',
        'owner',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'duration' => 'integer',
            'adults' => 'integer',
            'children' => 'integer',
        ];
    }

    protected $hidden = ['dmc_payload_hash'];

    public function assignedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function crmActivities() { return $this->hasMany(CrmActivity::class); }

    // Coloured stage badge (matches theme)
    public function stageBadge(): string
    {
        $map = [
            'New'         => 'info',
            'Contacted'   => 'warning',
            'Proposal'    => 'primary',
            'Negotiation' => 'warning',
            'Won'         => 'success',
            'Lost'        => 'danger',
        ];
        $class = $map[$this->stage] ?? 'info';
        return "<span class='bullet-badge bullet-badge-{$class}'>{$this->stage}</span>";
    }
}
