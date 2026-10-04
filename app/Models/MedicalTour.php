<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalTour extends Model
{
    protected $fillable = [
        'customer_id', 'patient_name', 'destination', 'hospital', 'treatment', 'cost', 'status',
        'service_fee',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
