<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A vacancy listed on the public careers page.
 */
class JobOpening extends Model
{
    protected $fillable = [
        'title', 'department', 'location', 'employment_type',
        'description', 'closing_date', 'sort_order', 'status',
    ];

    protected $casts = [
        'closing_date' => 'date',
        'sort_order'   => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    /** Hide vacancies whose closing date has passed. */
    public function scopeOpen($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('closing_date')->orWhereDate('closing_date', '>=', now()->toDateString());
        });
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
