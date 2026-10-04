<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One day of a tour package's day-by-day plan.
 */
class PackageItinerary extends Model
{
    protected $fillable = [
        'package_id', 'day_number', 'title', 'description',
    ];

    protected $casts = [
        'day_number' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
