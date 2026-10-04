<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuide extends Model
{
    protected $fillable = [
        'name', 'photo', 'phone', 'languages', 'experience_years', 'bio', 'user_id', 'status',
    ];

    public function assignments()
    {
        return $this->hasMany(TourGuideAssignment::class);
    }

    public function ratings()
    {
        return $this->hasMany(TourGuideRating::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The status the UI should show.
     *
     * `status` is what the admin picked (active / inactive — an employment
     * fact). Whether the guide is out on a tour right now is a fact of the
     * assignment calendar, so it wins over the stored value: an inactive
     * guide stays inactive, everyone else shows on_tour while a live
     * assignment covers today.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'inactive') {
            return 'inactive';
        }

        $onTour = $this->assignments
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->contains(fn ($a) => $a->start_date->lte(today()) && $a->end_date->gte(today()));

        return $onTour ? 'on_tour' : 'active';
    }

    public function getAvgRatingAttribute(): ?float
    {
        $avg = $this->ratings->avg('rating');

        return $avg === null ? null : round((float) $avg, 1);
    }

    /** Does a new assignment overlap one this guide already has? */
    public function hasOverlap(string $start, string $end, ?int $ignoreId = null): bool
    {
        return $this->assignments()
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $start)
            ->exists();
    }
}
