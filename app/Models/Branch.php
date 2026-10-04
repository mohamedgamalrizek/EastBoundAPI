<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name', 'code', 'manager_name', 'phone', 'email', 'city', 'address', 'status',
    ];

    protected $casts = [
    ];
}
