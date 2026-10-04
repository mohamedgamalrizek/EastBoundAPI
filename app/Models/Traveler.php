<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traveler extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'relation', 'passport_no', 'nationality', 'dob', 'status',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
