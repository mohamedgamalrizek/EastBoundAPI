<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlan extends Model
{
    protected $fillable = ['customer_id', 'destination', 'start_date', 'days', 'budget', 'interests', 'plan'];
    protected $casts = ['start_date' => 'date', 'plan' => 'array'];
    public function customer() { return $this->belongsTo(Customer::class); }
}
