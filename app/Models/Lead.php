<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Lead extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Lead')->logOnly(['name', 'stage', 'value'])->setDescriptionForEvent(fn (string $event) => $event);
    }

    protected $fillable = [
        'assigned_to',
        'name',
        'phone',
        'email',
        'interest',
        'source',
        'value',
        'stage',
        'owner',
        'notes',
    ];

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
