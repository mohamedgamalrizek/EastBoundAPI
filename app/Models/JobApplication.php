<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_opening_id',
        'name',
        'email',
        'phone',
        'linkedin_url',
        'resume_path',
        'cover_letter',
        'status',
    ];

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function statusBadge(): string
    {
        $map = [
            'new'         => 'warning',
            'reviewed'    => 'info',
            'shortlisted' => 'success',
            'rejected'    => 'danger',
            'hired'       => 'success',
        ];

        $class = $map[$this->status] ?? 'warning';

        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . '</span>';
    }
}
