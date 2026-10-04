<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CorporateTravel extends Model
{
    protected $table = 'corporate_travels';

    protected $fillable = [
        'customer_id', 'company_name', 'contact_person', 'service_type', 'employees', 'budget', 'status',
        'service_fee',
    ];

    protected $casts = [
        'service_fee' => 'decimal:2',
        'budget' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
