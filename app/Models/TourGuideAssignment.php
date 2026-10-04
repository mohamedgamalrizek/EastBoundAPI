<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuideAssignment extends Model
{
    protected $fillable = [
        'tour_guide_id', 'package_id', 'tour_schedule_id',
        'start_date', 'end_date', 'status', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function guide()
    {
        return $this->belongsTo(TourGuide::class, 'tour_guide_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function schedule()
    {
        return $this->belongsTo(TourSchedule::class, 'tour_schedule_id');
    }

    /** Live assignments (not finished, not cancelled). */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress']);
    }

    /** Assignments whose window covers the given date. */
    public function scopeCovering($query, $date)
    {
        return $query->where('start_date', '<=', $date)->where('end_date', '>=', $date);
    }

    /** Badge colour for the status. */
    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'scheduled'   => 'info',
            'in_progress' => 'warning',
            'completed'   => 'success',
            default       => 'secondary',
        };
    }
}
