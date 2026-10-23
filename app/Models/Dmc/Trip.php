<?php

namespace App\Models\Dmc;

use App\Models\Customer;
use App\Models\User;

class Trip extends DmcModel
{
    protected $table = 'dmc_trips';

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function items()
    {
        return $this->hasMany(TripItem::class);
    }

    public function requests()
    {
        return $this->hasMany(PurchaseRequest::class);
    }

    public function alerts()
    {
        return $this->hasMany(TripAlert::class);
    }
}
