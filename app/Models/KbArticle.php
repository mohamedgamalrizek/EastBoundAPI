<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbArticle extends Model
{
    protected $fillable = [
        'user_id',
        'title', 'category', 'excerpt', 'views', 'status',
    ];

    protected $casts = [
        'views'      => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
