<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    protected $fillable = ['customer_id', 'points', 'type', 'description', 'source_type', 'source_id', 'reference'];
    public function customer() { return $this->belongsTo(Customer::class); }
    public function source() { return $this->morphTo(); }
}
