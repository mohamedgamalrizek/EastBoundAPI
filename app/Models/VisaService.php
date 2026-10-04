<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A visa product advertised on the public site. Not to be confused with
 * VisaApplication, which is a customer's submitted case.
 */
class VisaService extends Model
{
    protected $fillable = [
        'country', 'flag', 'visa_type', 'processing_time', 'stay_duration',
        'entry_type', 'govt_fee', 'service_fee',
        'fee', 'requirements', 'is_featured', 'sort_order', 'status',
    ];

    protected $casts = [
        'fee'         => 'decimal:2',
        'govt_fee'    => 'decimal:2',
        'service_fee' => 'decimal:2',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public const ENTRY_TYPES = ['Single', 'Multiple'];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /** Ordered the way the public page shows them. */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('country');
    }

    /**
     * Published price. `fee` is kept in sync with the two parts on save, so
     * reading it stays correct for callers that only want the total.
     */
    public function totalFee(): float
    {
        return (float) $this->govt_fee + (float) $this->service_fee;
    }

    /** Requirements are stored one per line. */
    public function requirementList(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->requirements))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
    }
}
