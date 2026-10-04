<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = [
        'title', 'image_label', 'image', 'category', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
