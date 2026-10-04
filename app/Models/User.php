<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\Status;
use App\Traits\CommonHelperTrait;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, LogsActivity, CommonHelperTrait;

    protected $fillable = ['name', 'email', 'password', 'commission_rate',];

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret',];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'permissions'       => 'array',
        'preferences'       => 'array',
        'status'            => Status::class,
        'gender'            => Gender::class
    ];

    protected $appends = ['profile_photo_url',];

    /**
     * Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('User')->logOnly(['name', 'email'])->setDescriptionForEvent(fn (string $eventName) => "{$eventName}");
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function image()
    {
        return $this->belongsTo(Upload::class, 'image_id', 'id');
    }

    /** The business record behind a customer-role login (website sign-ups). */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function nid()
    {
        return $this->belongsTo(Upload::class, 'nid', 'id');
    }

    /* ---------------------------------------------------------------------
     | Relations — agent earnings, HR records, authored content & assignments.
     * ------------------------------------------------------------------- */
    public function agentCommissions()        { return $this->hasMany(AgentCommission::class, 'agent_id'); }
    public function agentWalletTransactions() { return $this->hasMany(AgentWalletTransaction::class, 'agent_id'); }
    public function agentWithdrawals()        { return $this->hasMany(AgentWithdrawal::class, 'agent_id'); }
    public function agentInvoices()           { return $this->hasMany(AgentInvoice::class, 'agent_id'); }
    public function staffAttendances()        { return $this->hasMany(StaffAttendance::class); }
    public function leaveRequests()           { return $this->hasMany(LeaveRequest::class); }
    public function payslips()                { return $this->hasMany(Payslip::class); }
    public function notifications()           { return $this->hasMany(Notification::class); }
    public function crmActivities()           { return $this->hasMany(CrmActivity::class); }
    public function blogs()                   { return $this->hasMany(Blog::class); }
    public function kbArticles()              { return $this->hasMany(KbArticle::class); }
    public function announcements()           { return $this->hasMany(Announcement::class); }
    public function assignedTasks()           { return $this->hasMany(Task::class, 'assigned_to'); }
    public function assignedTickets()         { return $this->hasMany(SupportTicket::class, 'assigned_to'); }
    public function assignedLeads()           { return $this->hasMany(Lead::class, 'assigned_to'); }

    /**
     * Quick check whether the user carries a given permission.
     */
    public function can_access(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }

    /**
     * Admin-type users (Super Admin, Admin, Manager, Operations, Accountant,
     * Support) all carry dashboard_read. Portal users (Agent, Customer) do not.
     */
    public function isAdminUser(): bool
    {
        return $this->can_access('dashboard_read');
    }

    /**
     * Where this user should land after login, based on their role.
     * Admin panel for staff, the matching portal for agents/customers.
     */
    public function home(): string
    {
        // SaaS platform owner → the separate SaaS panel (only when SaaS mode is on).
        if (config('saas.enabled') && $this->can_access('saas_read')) {
            return route('saas.dashboard');
        }

        if ($this->isAdminUser()) {
            return route('dashboard');
        }

        if ($this->can_access('agent_portal_read')) {
            return route('agent.dashboard');
        }

        if ($this->can_access('customer_portal_read')) {
            return route('cust.dashboard');
        }

        if ($this->can_access('staff_portal_read')) {
            return route('staff.dashboard');
        }

        return route('home');
    }
}
