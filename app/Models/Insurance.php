<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['customer_id' => \App\Models\Customer::class];
    }

    protected $fillable = [
        'customer_id', 'provider', 'plan_name', 'type', 'coverage', 'premium', 'status',
        'service_fee',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'coverage' => 'decimal:2',
        'premium' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
