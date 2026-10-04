<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HajjPackage extends Model
{
    protected $fillable = [
        'package_no', 'title', 'type', 'duration_days',
        'price', 'seats', 'status',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'price'         => 'decimal:2',
        'seats'         => 'integer',
    ];

    public function pilgrims() { return $this->hasMany(HajjPilgrim::class); }
}
