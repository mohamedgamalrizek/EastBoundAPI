<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourGuideRating extends Model
{
    protected $fillable = [
        'tour_guide_id', 'customer_id', 'tour_guide_assignment_id', 'rating', 'comment',
    ];

    public function guide()
    {
        return $this->belongsTo(TourGuide::class, 'tour_guide_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignment()
    {
        return $this->belongsTo(TourGuideAssignment::class, 'tour_guide_assignment_id');
    }
}
