<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['customer_id' => \App\Models\Customer::class, 'lead_id' => \App\Models\Lead::class];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('lead_access', function ($query) {
            if (auth()->check() && ! auth()->user()->can_access('lead_view_all')) {
                $query->where(function ($query) {
                    $query->whereNull('lead_id')->orWhereIn('lead_id', Lead::select('id'));
                });
            }
        });
    }

    protected $fillable = [
        'customer_id', 'user_id', 'lead_id',
        'customer_name', 'type', 'subject', 'body', 'activity_date', 'channel',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Lead::class);
    }
}
