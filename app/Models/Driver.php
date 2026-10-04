<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'license_no', 'vehicle', 'status',
    ];

    public function bookings()
    {
        return $this->hasMany(TransportBooking::class);
    }
}
