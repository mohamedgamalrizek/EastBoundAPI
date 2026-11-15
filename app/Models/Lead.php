<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use LogsActivity;
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope('lead_access', function ($query) {
            \App\Support\LeadAccess::constrain($query);
        });
    }

    public const LANGUAGES = ['English', 'Spanish', 'Italian', 'French'];

    public const CAMPAIGN_SOURCES = ['Google Ads', 'Social Media', 'Organic Leads', 'Referral', 'Other'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Lead')->logOnly(['assigned_to', 'owner', 'name', 'stage', 'value', 'website', 'dmc_website_id', 'language', 'country', 'campaign_id', 'campaign_source', 'lost_reason', 'created_at'])->logOnlyDirty()->setDescriptionForEvent(fn (string $event) => $event);
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

    public function scopeFiltered($query, array $filters)
    {
        if (! empty($filters['search'])) {
            $query->where(function ($search) use ($filters) {
                foreach (['name', 'phone', 'email', 'subject', 'interest', 'destinations'] as $column) {
                    $search->orWhere($column, 'like', '%'.$filters['search'].'%');
                }
            });
        }
        foreach (['stage', 'source', 'language', 'country', 'website', 'campaign_source', 'owner'] as $column) {
            if (isset($filters[$column]) && $filters[$column] !== '') {
                $query->where($column, $filters[$column]);
            }
        }
        if (! empty($filters['assigned_to'])) {
            $filters['assigned_to'] === 'unassigned'
                ? $query->whereNull('assigned_to')
                : $query->where('assigned_to', $filters['assigned_to']);
        }
        foreach (['created_from' => ['created_at', '>='], 'created_to' => ['created_at', '<='],
                  'travel_from' => ['from_date', '>='], 'travel_to' => ['from_date', '<=']] as $key => [$column, $operator]) {
            if (! empty($filters[$key])) {
                $query->whereDate($column, $operator, $filters[$key]);
            }
        }
        foreach (['value_min' => '>=', 'value_max' => '<='] as $key => $operator) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $query->where('value', $operator, $filters[$key]);
            }
        }
        $sorts = ['newest' => ['created_at', 'desc'], 'oldest' => ['created_at', 'asc'],
            'value_high' => ['value', 'desc'], 'value_low' => ['value', 'asc'], 'name' => ['name', 'asc']];
        [$column, $direction] = $sorts[$filters['sort'] ?? 'newest'] ?? $sorts['newest'];
        return $query->orderBy($column, $direction)->orderByDesc('id');
    }

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
