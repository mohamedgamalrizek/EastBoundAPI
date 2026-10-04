<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = [
        'user_id',
        'title', 'slug', 'author', 'category', 'image',
        'excerpt', 'body', 'read_minutes', 'status', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'date',
        'read_minutes' => 'integer',
    ];

    /** Only rows the public blog should ever see. */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Named authorUser() (not author()) because the legacy display column
    // `author` (string) would otherwise shadow the relation accessor.
    public function authorUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
