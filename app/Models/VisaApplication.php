<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisaApplication extends Model
{
    protected $fillable = [
        'customer_id', 'package_id', 'visa_service_id',
        'application_no', 'applicant_name', 'country', 'visa_type',
        'govt_fee', 'service_fee',
        'applied_date', 'appointment_date', 'expiry_date',
        'embassy_center', 'appointment_time', 'appointment_status', 'appointment_notes',
        'documents_status', 'status',
    ];

    protected $casts = [
        'applied_date'     => 'date',
        'appointment_date' => 'date',
        'expiry_date'      => 'date',
        'govt_fee'         => 'decimal:2',
        'service_fee'      => 'decimal:2',
    ];

    /** What the applicant pays in total for this case. */
    public function totalFee(): float
    {
        return (float) $this->govt_fee + (float) $this->service_fee;
    }

    /**
     * How far along the case is, as a percentage.
     *
     * Derived from the two status columns rather than stored, so it can never
     * disagree with them: a case only moves once its documents do.
     */
    public function progress(): int
    {
        return self::progressFor($this->status, $this->documents_status);
    }

    /**
     * How far along a case with this status and paperwork is.
     *
     * Kept as a static rule rather than reading $this so the status-tracking
     * screen can show the same number for a status the user has picked but not
     * saved yet — the bar moving with the dropdown is the whole point of that
     * screen, and JS mirrors these steps.
     */
    public static function progressFor(?string $status, ?string $documentsStatus): int
    {
        if (in_array($status, ['Approved', 'Rejected'], true)) {
            return 100;
        }

        // Once the file is with the embassy the paperwork question is settled.
        if ($status === 'In Review') {
            return 75;
        }

        // Still on the desk (Processing): progress is how far the documents got.
        return match ($documentsStatus) {
            'Verified'  => 60,
            'Submitted' => 40,
            default     => 15,
        };
    }

    /** Bootstrap contextual colour for the current status. */
    public function statusTone(): string
    {
        return match ($this->status) {
            'Approved' => 'success',
            'Rejected' => 'danger',
            'In Review' => 'info',
            default    => 'warning',
        };
    }

    /** Whole days until the visa expires; negative once it has. */
    public function daysToExpiry(): ?int
    {
        return $this->expiry_date
            ? (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false)
            : null;
    }

    /** Urgency band used by the expiry list. */
    public function expiryTone(): string
    {
        $days = $this->daysToExpiry();

        return match (true) {
            $days === null => 'secondary',
            $days < 0      => 'secondary',
            $days <= 15    => 'danger',
            $days <= 30    => 'warning',
            default        => 'success',
        };
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class);
    }

    /** Catalogue entry this case was sold from. */
    public function visaService()
    {
        return $this->belongsTo(\App\Models\VisaService::class);
    }

    public function documents()
    {
        return $this->hasMany(VisaDocument::class);
    }
}
