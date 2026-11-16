<?php

namespace App\Models\Dmc;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class Opportunity extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['lead_id' => \App\Models\Lead::class, 'customer_id' => \App\Models\Customer::class];
    }

    public static function constrainRecordAccess($query): void
    {
        if (! \App\Support\CustomerAccess::restricted() || auth()->user()->can_access('opportunity_view_all')) return;

        // Visibility follows the lead assignee, independently of lead/customer view-all.
        $query->where(function ($query) {
            $query->whereIn('dmc_opportunities.lead_id', Lead::withoutGlobalScope('lead_access')
                ->withTrashed()->where('leads.assigned_to', auth()->id())->select('leads.id'))
                ->orWhere(fn ($query) => $query->whereNull('dmc_opportunities.lead_id')
                    ->where('dmc_opportunities.assigned_to', auth()->id()));
        });
    }

    protected $table = 'dmc_opportunities';

    protected $casts = ['expected_value' => 'decimal:2', 'expected_close' => 'date'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}
