<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A customer's verdict on a tour they actually took.
 *
 * "Verified" is not a flag somebody ticks: it is whether the review is
 * attached to a booking. A customer can only write one against a paid booking
 * of theirs whose travel date has passed (see ReviewEligibility), so the badge
 * on the public page is a fact about the record, not a claim.
 */
class Review extends Model
{
    public const PENDING  = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    public const STATUSES = [self::PENDING, self::APPROVED, self::REJECTED];

    protected $fillable = [
        'customer_id', 'package_id', 'booking_id',
        'rating', 'title', 'comment',
        'status', 'reply', 'replied_at',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'replied_at' => 'datetime',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function package()  { return $this->belongsTo(Package::class); }
    public function booking()  { return $this->belongsTo(Booking::class); }

    /* ---- Scopes ---------------------------------------------------------- */

    public function scopeApproved($query) { return $query->where('status', self::APPROVED); }

    public function scopeVerified($query) { return $query->whereNotNull('booking_id'); }

    /* ---- Presentation ---------------------------------------------------- */

    /** Written against a real booking, so the trip demonstrably happened. */
    public function isVerified(): bool
    {
        return $this->booking_id !== null;
    }

    /** Who to show as the author — falls back rather than printing nothing. */
    public function authorName(): string
    {
        return $this->customer?->name ?: 'Traveller';
    }

    public function statusBadge(): string
    {
        $map = [
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        ];
        $class = $map[$this->status] ?? 'warning';

        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . '</span>';
    }

    public function statusTone(): string
    {
        return [
            self::PENDING  => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        ][$this->status] ?? 'warning';
    }
}
