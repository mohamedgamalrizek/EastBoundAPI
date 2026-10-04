<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Customer is the B2C account. It is now Authenticatable + HasApiTokens so a
 * customer can log in to the FLOW mobile app and receive a Sanctum token.
 * It remains a normal Eloquent model for the web ERP (Authenticatable extends
 * Model), so existing admin code keeps working unchanged.
 */
class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Customer')->logOnly(['name', 'email', 'status'])->setDescriptionForEvent(fn (string $event) => $event);
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'address',
        'gender',
        'date_of_birth',
        'tier',
        'referral_code',
        'referred_by',
        'status',
        'notes',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'password'          => 'hashed',
        'date_of_birth'     => 'date',
        'email_verified_at' => 'datetime',
        'otp_expires_at'    => 'datetime',
        'preferences'       => 'array',
    ];

    /* ---------------------------------------------------------------------
     | Relations — a customer is the hub many records link back to.
     * ------------------------------------------------------------------- */
    /** The portal login attached to this customer, if they registered online. */
    public function user()              { return $this->hasOne(User::class, 'customer_id'); }
    public function bookings()          { return $this->hasMany(Booking::class); }
    public function visaApplications()  { return $this->hasMany(VisaApplication::class); }
    public function flightBookings()    { return $this->hasMany(FlightBooking::class); }
    public function transportBookings() { return $this->hasMany(TransportBooking::class); }
    public function hajjPilgrims()      { return $this->hasMany(HajjPilgrim::class); }
    public function hotelBookings()     { return $this->hasMany(HotelBooking::class); }
    public function travelers()         { return $this->hasMany(Traveler::class); }
    public function passports()         { return $this->hasMany(Passport::class); }
    public function documents()         { return $this->hasMany(CustomerDocument::class); }
    public function walletTransactions(){ return $this->hasMany(WalletTransaction::class); }
    public function wishlists()         { return $this->hasMany(Wishlist::class); }
    public function tripPlans()         { return $this->hasMany(TripPlan::class); }
    public function loyaltyTransactions(){ return $this->hasMany(LoyaltyTransaction::class); }
    public function reviews()           { return $this->hasMany(Review::class); }
    public function referrer()          { return $this->belongsTo(Customer::class, 'referred_by'); }
    public function referrals()         { return $this->hasMany(Customer::class, 'referred_by'); }

    // Side services sold to this customer — each carries its own service fee.
    public function insurances()        { return $this->hasMany(Insurance::class); }
    public function studentServices()   { return $this->hasMany(StudentService::class); }
    public function medicalTours()      { return $this->hasMany(MedicalTour::class); }
    public function corporateTravels()  { return $this->hasMany(CorporateTravel::class); }
    public function crmActivities()     { return $this->hasMany(CrmActivity::class); }
    public function supportTickets()    { return $this->hasMany(SupportTicket::class); }
    public function invoices()          { return $this->hasMany(Invoice::class); }
    public function receipts()          { return $this->hasMany(Receipt::class); }
    public function agentCommissions()  { return $this->hasMany(AgentCommission::class); }

    // Status badge (theme styling)
    public function statusBadge(): string
    {
        $class = $this->status === 'active' ? 'success' : 'danger';
        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . "</span>";
    }

    // Tier badge
    public function tierBadge(): string
    {
        $map = ['Silver' => 'info', 'Gold' => 'warning', 'Platinum' => 'success'];
        $class = $map[$this->tier] ?? 'info';
        return "<span class='bullet-badge bullet-badge-{$class}'>{$this->tier}</span>";
    }
}
