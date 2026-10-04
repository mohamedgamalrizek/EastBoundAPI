<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'assigned_to', 'title', 'project', 'priority', 'due_date', 'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function assignedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }
}
