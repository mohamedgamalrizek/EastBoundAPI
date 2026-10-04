<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An agreement with a supplier: rates, period and the signed paperwork. */
class SupplierContract extends Model
{
    protected $fillable = [
        'supplier_id', 'contract_no', 'title', 'rate_type', 'value',
        'commission_rate', 'credit_days', 'start_date', 'end_date',
        'terms', 'document', 'status',
    ];

    protected $casts = [
        'value'           => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'start_date'      => 'date',
        'end_date'        => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions()
    {
        return $this->hasMany(SupplierTransaction::class);
    }

    /** Past its end date but still marked Active. */
    public function isLapsed(): bool
    {
        return $this->end_date && $this->end_date->isPast() && $this->status === 'Active';
    }
}
