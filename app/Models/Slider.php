<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'image_label', 'image',
        'badge', 'cta_text', 'cta_link', 'sort_order', 'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];
}
