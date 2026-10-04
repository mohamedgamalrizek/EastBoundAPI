<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $table = 'staff_attendance';

    protected $fillable = [
        'user_id',
        'staff_name', 'date', 'check_in', 'check_out', 'hours', 'status',
    ];

    protected $casts = [
        'date'  => 'date',
        'hours' => 'decimal:2',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
