<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'user_id',
        'title', 'body', 'audience', 'published_on', 'status',
    ];

    protected $casts = [
        'published_on' => 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function author(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
