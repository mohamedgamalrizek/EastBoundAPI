<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CommonHelperTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouteList extends Model
{
    use HasFactory, CommonHelperTrait;

    protected $casts = [
        'status' => Status::class,
    ];
}
