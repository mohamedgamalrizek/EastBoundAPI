<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $fillable = [
        'customer_id',
        'package_id',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function package()  { return $this->belongsTo(Package::class); }
}
