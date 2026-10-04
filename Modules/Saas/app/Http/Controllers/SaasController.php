<?php

namespace Modules\Saas\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Modules\Saas\Http\Requests\SignupRequest;
use Modules\Saas\Models\Plan;
use Modules\Saas\Models\Tenant;
use Modules\Saas\Models\Subscription;
use Modules\Saas\Models\Payment;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Modules\Saas\Http\Requests\PlanRequest;
use Modules\Saas\Http\Requests\TenantRequest;
use Modules\Saas\Http\Requests\SubscriptionRequest;
use Modules\Saas\Http\Requests\DomainRequest;

class SaasController extends Controller
{
    public const BILLING_CYCLES = ['monthly', 'yearly', 'lifetime'];
    public const TENANT_STATUSES = ['active', 'suspended', 'pending'];
    public const SUBSCRIPTION_STATUSES = ['active', 'trial', 'cancelled', 'expired'];
    public const SIGNUP_DOMAIN_BASE = 'flow.test';

    /* =====================================================================
     | Public marketing landing — hero, features, pricing (live plans), FAQ.
     | The front door of the SaaS product; CTAs flow into tenant-signup.
     * ===================================================================== */

    public function landing()
    {
        return view('saas::saas.landing', [
            'plans'      => Plan::where('status', 1)->orderBy('price')->get(),
            'tenants'    => Tenant::where('status', 'active')->count(),
            'domainBase' => self::SIGNUP_DOMAIN_BASE,
        ]);
    }

    /* =====================================================================
     | Public self-serve signup (guest) — create + provision a new tenant
     * ===================================================================== */

    public function signup()
    {
        return view('saas::saas.signup', [
            'plans'      => Plan::where('status', 1)->orderBy('price')->get(),
            'domainBase' => self::SIGNUP_DOMAIN_BASE,
        ]);
    }

    public function signupStore(SignupRequest $request)
    {
        $domain = Str::lower($request->subdomain) . '.' . self::SIGNUP_DOMAIN_BASE;

        if (Domain::where('domain', $domain)->exists()) {
            return back()->withInput()->with('danger', "The subdomain '{$request->subdomain}' is already taken.");
        }

        $plan = Plan::findOrFail($request->plan_id);

        // 1. Central tenant record (+ domain + subscription)
        $tenant = Tenant::withoutEvents(fn () => Tenant::create([
            'id'      => (string) Str::uuid(),
            'name'    => $request->company,
            'email'   => $request->email,
            'plan_id' => $plan->id,
            'status'  => 'active',
        ]));
        $tenant->domains()->create(['domain' => $domain]);
        Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id'   => $plan->id,
            'status'    => 'active',
            'starts_at' => now(),
            'ends_at'   => $this->cycleEnd($plan->billing_cycle),
            'amount'    => $plan->price,
        ]);

        // 2. Provision the tenant's isolated database
        if ($error = $this->provision($tenant)) {
            // Roll the central records back so a failed provision doesn't orphan them.
            Tenant::withoutEvents(function () use ($tenant) {
                $tenant->domains()->delete();
                $tenant->subscriptions()->delete();
                $tenant->delete();
            });
            return back()->withInput()->with('danger', "Could not provision your workspace: {$error}");
        }

        // 3. Seed the tenant's first user (the owner) inside the tenant database.
        $tenant->run(function () use ($request) {
            DB::table('users')->insert([
                'name'       => $request->company . ' Owner',
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'role'       => 'owner',
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $this->log('created', $tenant, "tenant self-signup: {$tenant->name}");

        return view('saas::saas.signup-success', [
            'company' => $request->company,
            'domain'  => $domain,
            'email'   => $request->email,
            'plan'    => $plan,
        ]);
    }

    /* =====================================================================
     | Dashboard
     * ===================================================================== */

    public function dashboard()
    {
        return view('saas::saas.dashboard', [
            'tenantCount'   => Tenant::count(),
            'activeTenants' => Tenant::where('status', 'active')->count(),
            'planCount'     => Plan::count(),
            'activeSubs'    => Subscription::where('status', 'active')->count(),
            'mrr'           => Subscription::where('status', 'active')->sum('amount'),
            'recentTenants' => Tenant::with('plan')->latest()->take(5)->get(),
        ]);
    }

    /* =====================================================================
     | Plans CRUD
     * ===================================================================== */

    public function plans()
    {
        return view('saas::saas.plans', ['plans' => Plan::withCount('tenants')->latest()->get()]);
    }

    public function planCreate()
    {
        return view('saas::saas.plan-create', ['plan' => null, 'cycles' => self::BILLING_CYCLES]);
    }

    public function planStore(PlanRequest $request)
    {
        $plan = Plan::create($this->planData($request));
        $this->log('created', $plan, "created Plan {$plan->name}");

        return redirect()->route('saas.plans')->with('success', 'Plan created.');
    }

    public function planEdit($id)
    {
        return view('saas::saas.plan-edit', ['plan' => Plan::findOrFail($id), 'cycles' => self::BILLING_CYCLES]);
    }

    public function planUpdate(PlanRequest $request)
    {
        $plan = Plan::findOrFail($request->id);
        $plan->update($this->planData($request));
        $this->log('updated', $plan, "updated Plan {$plan->name}");

        return redirect()->route('saas.plans')->with('success', 'Plan updated.');
    }

    public function planDelete($id)
    {
        $plan = Plan::withCount('tenants')->findOrFail($id);

        if ($plan->tenants_count > 0) {
            return response()->json(['status' => false, 'message' => 'Plan is assigned to tenants and cannot be deleted.', 'status_code' => 400], 400);
        }

        $this->log('deleted', $plan, "deleted Plan {$plan->name}");
        $plan->delete();

        return response()->json(['status' => true, 'message' => 'Plan deleted.', 'status_code' => 200]);
    }

    private function planData(Request $request): array
    {
        return [
            'name'          => $request->name,
            'slug'          => Str::slug($request->name),
            'price'         => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'max_users'     => $request->max_users ?: null,
            'features'      => collect(preg_split('/\r\n|\r|\n/', (string) $request->features))
                ->map(fn ($f) => trim($f))->filter()->values()->all(),
            'status'        => (int) $request->status,
        ];
    }

    /* =====================================================================
     | Tenants CRUD (+ status toggle)
     * ===================================================================== */

    public function tenants()
    {
        $tenants = Tenant::with(['plan', 'domains'])->latest()->get();

        return view('saas::saas.tenants', [
            'tenants'     => $tenants,
            'provisioned' => $this->provisionedDatabases(),
        ]);
    }

    public function tenantCreate()
    {
        return view('saas::saas.tenant-create', [
            'tenant'   => null,
            'plans'    => Plan::where('status', 1)->orderBy('name')->get(),
            'statuses' => self::TENANT_STATUSES,
        ]);
    }

    public function tenantStore(TenantRequest $request)
    {
        // Create the tenant WITHOUT firing stancl provisioning events — keeps
        // central-DB tenant management fast and safe. Spinning up the actual
        // per-tenant database is a separate, explicit deploy-time step.
        $tenant = Tenant::withoutEvents(function () use ($request) {
            return Tenant::create([
                'id'      => (string) Str::uuid(),
                'name'    => $request->name,
                'email'   => $request->email,
                'plan_id' => $request->plan_id ?: null,
                'status'  => $request->status,
            ]);
        });

        if ($request->filled('domain')) {
            $tenant->domains()->create(['domain' => Str::lower($request->domain)]);
        }

        // Mirror the plan onto an initial subscription so billing is tracked.
        if ($request->plan_id && ($plan = Plan::find($request->plan_id))) {
            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id'   => $plan->id,
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => $this->cycleEnd($plan->billing_cycle),
                'amount'    => $plan->price,
            ]);
        }

        $this->log('created', $tenant, "created Tenant {$tenant->name}");

        // Optionally spin up the tenant's isolated database immediately.
        if ($request->boolean('provision')) {
            if ($error = $this->provision($tenant)) {
                return redirect()->route('saas.tenants')->with('danger', "Tenant created, but provisioning failed: {$error}");
            }
            return redirect()->route('saas.tenants')->with('success', 'Tenant created and database provisioned.');
        }

        return redirect()->route('saas.tenants')->with('success', 'Tenant created.');
    }

    /** Provision (or re-provision) a tenant's isolated database on demand. */
    public function tenantProvision($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($this->isProvisioned($tenant)) {
            return back()->with('success', 'Tenant database already provisioned.');
        }

        if ($error = $this->provision($tenant)) {
            return back()->with('danger', "Provisioning failed: {$error}");
        }

        $this->log('updated', $tenant, "provisioned database for Tenant {$tenant->name}");

        return back()->with('success', 'Tenant database provisioned.');
    }

    public function tenantEdit($id)
    {
        return view('saas::saas.tenant-edit', [
            'tenant'   => Tenant::with('domains')->findOrFail($id),
            'plans'    => Plan::where('status', 1)->orderBy('name')->get(),
            'statuses' => self::TENANT_STATUSES,
        ]);
    }

    public function tenantUpdate(TenantRequest $request)
    {
        $tenant = Tenant::findOrFail($request->id);
        Tenant::withoutEvents(function () use ($tenant, $request) {
            $tenant->update([
                'name'    => $request->name,
                'email'   => $request->email,
                'plan_id' => $request->plan_id ?: null,
                'status'  => $request->status,
            ]);
        });

        $this->log('updated', $tenant, "updated Tenant {$tenant->name}");

        return redirect()->route('saas.tenants')->with('success', 'Tenant updated.');
    }

    public function tenantToggle($id)
    {
        $tenant = Tenant::findOrFail($id);
        $new = $tenant->status === 'active' ? 'suspended' : 'active';
        Tenant::withoutEvents(fn () => $tenant->update(['status' => $new]));
        $this->log('updated', $tenant, "{$new} Tenant {$tenant->name}");

        return back()->with('success', "Tenant {$new}.");
    }

    public function tenantDelete($id)
    {
        $tenant = Tenant::findOrFail($id);

        // Drop the tenant's isolated database if it was ever provisioned.
        if ($this->isProvisioned($tenant)) {
            DB::statement('DROP DATABASE IF EXISTS `' . $this->tenantDbName($tenant) . '`');
        }

        Tenant::withoutEvents(function () use ($tenant) {
            $tenant->domains()->delete();
            $tenant->subscriptions()->delete();
            $tenant->delete();
        });
        $this->log('deleted', $tenant, "deleted Tenant {$tenant->name}");

        return response()->json(['status' => true, 'message' => 'Tenant deleted.', 'status_code' => 200]);
    }

    /* ---- Tenant database provisioning helpers ---- */

    private function tenantDbName(Tenant $tenant): string
    {
        return config('tenancy.database.prefix', 'tenant') . $tenant->id . config('tenancy.database.suffix', '');
    }

    private function isProvisioned(Tenant $tenant): bool
    {
        return DB::table('information_schema.schemata')
            ->where('schema_name', $this->tenantDbName($tenant))->exists();
    }

    /** Map of tenant_id => true for every tenant whose database exists. */
    private function provisionedDatabases(): array
    {
        $prefix = config('tenancy.database.prefix', 'tenant');
        $suffix = config('tenancy.database.suffix', '');

        // information_schema columns are upper-case; alias to a predictable key.
        $rows = DB::select(
            'SELECT SCHEMA_NAME AS name FROM information_schema.schemata WHERE SCHEMA_NAME LIKE ?',
            [$prefix . '%']
        );

        $map = [];
        foreach ($rows as $row) {
            $id = Str::after($row->name, $prefix);
            if ($suffix !== '') {
                $id = Str::beforeLast($id, $suffix);
            }
            $map[$id] = true;
        }
        return $map;
    }

    /** Create the tenant's database + run tenant migrations + seed defaults. Returns null on success, else an error string. */
    private function provision(Tenant $tenant): ?string
    {
        try {
            if (! $this->isProvisioned($tenant)) {
                dispatch_sync(new CreateDatabase($tenant));
            }
            dispatch_sync(new MigrateDatabase($tenant));

            // Seed the tenant's own General Settings (name, logo, favicon, …)
            // INSIDE the tenant database so every new workspace starts branded.
            $tenant->run(function () {
                app(\Modules\Saas\Database\Seeders\TenantSettingSeeder::class)->run();
            });

            return null;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    /* =====================================================================
     | Subscriptions CRUD
     * ===================================================================== */

    public function subscriptions()
    {
        return view('saas::saas.subscriptions', ['subscriptions' => Subscription::with(['tenant', 'plan'])->latest()->get()]);
    }

    public function subscriptionCreate()
    {
        return view('saas::saas.subscription-create', $this->subscriptionFormData(null));
    }

    public function subscriptionStore(SubscriptionRequest $request)
    {
        $sub = Subscription::create($this->subscriptionData($request));
        $this->log('created', $sub, "created Subscription #{$sub->id}");

        return redirect()->route('saas.subscriptions')->with('success', 'Subscription created.');
    }

    public function subscriptionEdit($id)
    {
        return view('saas::saas.subscription-edit', $this->subscriptionFormData(Subscription::findOrFail($id)));
    }

    public function subscriptionUpdate(SubscriptionRequest $request)
    {
        $sub = Subscription::findOrFail($request->id);
        $sub->update($this->subscriptionData($request));
        $this->log('updated', $sub, "updated Subscription #{$sub->id}");

        return redirect()->route('saas.subscriptions')->with('success', 'Subscription updated.');
    }

    public function subscriptionDelete($id)
    {
        $sub = Subscription::findOrFail($id);
        $this->log('deleted', $sub, "deleted Subscription #{$sub->id}");
        $sub->delete();

        return response()->json(['status' => true, 'message' => 'Subscription deleted.', 'status_code' => 200]);
    }

    /**
     * Renew a subscription: extend the period by the plan's billing cycle,
     * mark active, reactivate the tenant, and record a payment.
     */
    public function subscriptionRenew($id)
    {
        $sub = Subscription::with(['plan', 'tenant'])->findOrFail($id);
        $cycle = $sub->plan?->billing_cycle;

        // Extend from the later of "now" or the current end date (so early
        // renewals stack remaining time instead of losing it).
        $base = ($sub->ends_at && $sub->ends_at->isFuture()) ? $sub->ends_at->copy() : now();

        $sub->update([
            'status'    => 'active',
            'starts_at' => $sub->starts_at ?: now(),
            'ends_at'   => $this->cycleEndFrom($base, $cycle),
            'amount'    => $sub->plan?->price ?? $sub->amount,
        ]);

        if ($sub->tenant_id) {
            Tenant::where('id', $sub->tenant_id)->update(['status' => 'active']);
        }

        $this->recordPayment($sub, 'renewal');
        $this->log('renewed', $sub, "renewed Subscription #{$sub->id}");

        return redirect()->route('saas.subscriptions')->with('success', 'Subscription renewed & tenant reactivated.');
    }

    /**
     * Upgrade / downgrade: switch the subscription (and tenant) to another plan.
     */
    public function subscriptionChangePlan(Request $request, $id)
    {
        $request->validate(['plan_id' => ['required', 'exists:plans,id']]);

        $sub  = Subscription::findOrFail($id);
        $plan = Plan::findOrFail($request->plan_id);

        $sub->update([
            'plan_id' => $plan->id,
            'amount'  => $plan->price,
            'status'  => 'active',
        ]);

        if ($sub->tenant_id) {
            Tenant::where('id', $sub->tenant_id)->update(['plan_id' => $plan->id, 'status' => 'active']);
        }

        $this->recordPayment($sub, 'plan-change');
        $this->log('plan-changed', $sub, "changed Subscription #{$sub->id} to plan {$plan->name}");

        return redirect()->route('saas.subscriptions')->with('success', "Plan changed to {$plan->name}.");
    }

    /** Record a payment row for a subscription action (renewal/upgrade). */
    private function recordPayment(Subscription $sub, string $kind): void
    {
        Payment::create([
            'gateway'         => 'manual',
            'reference'       => strtoupper($kind) . '-' . $sub->id . '-' . now()->format('YmdHis'),
            'tenant_id'       => $sub->tenant_id,
            'plan_id'         => $sub->plan_id,
            'subscription_id' => $sub->id,
            'amount'          => $sub->amount,
            'currency'        => 'BDT',
            'status'          => 'paid',
            'paid_at'         => now(),
        ]);
    }

    private function subscriptionFormData($subscription): array
    {
        return [
            'subscription' => $subscription,
            'tenants'      => Tenant::orderBy('name')->get(),
            'plans'        => Plan::orderBy('name')->get(),
            'statuses'     => self::SUBSCRIPTION_STATUSES,
        ];
    }

    private function subscriptionData(Request $request): array
    {
        $amount = $request->amount;
        if (! $amount && ($plan = Plan::find($request->plan_id))) {
            $amount = $plan->price;
        }

        return [
            'tenant_id' => $request->tenant_id,
            'plan_id'   => $request->plan_id,
            'status'    => $request->status,
            'starts_at' => $request->starts_at ?: null,
            'ends_at'   => $request->ends_at ?: null,
            'amount'    => $amount ?: 0,
        ];
    }

    /* =====================================================================
     | Domains CRUD
     * ===================================================================== */

    public function domains()
    {
        return view('saas::saas.domains', ['domains' => Domain::with('tenant')->latest()->get()]);
    }

    public function domainCreate()
    {
        return view('saas::saas.domain-create', ['tenants' => Tenant::orderBy('name')->get()]);
    }

    public function domainStore(DomainRequest $request)
    {
        Domain::create([
            'domain'    => Str::lower($request->domain),
            'tenant_id' => $request->tenant_id,
        ]);

        return redirect()->route('saas.domains')->with('success', 'Domain added.');
    }

    public function domainDelete($id)
    {
        Domain::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Domain removed.', 'status_code' => 200]);
    }

    /* =====================================================================
     | Platform pages (data-driven)
     * ===================================================================== */

    public function invoices()
    {
        // Invoices are derived from subscriptions until a billing gateway is wired.
        return view('saas::saas.invoices', [
            'subscriptions' => Subscription::with(['tenant', 'plan'])->latest('starts_at')->get(),
            'totalBilled'   => (float) Subscription::sum('amount'),
        ]);
    }

    public function payments()
    {
        $paid = Subscription::whereIn('status', ['active', 'expired'])->with(['tenant', 'plan'])->latest('starts_at')->get();

        return view('saas::saas.payments', [
            'payments'  => $paid,
            'totalPaid' => (float) $paid->sum('amount'),
            'pending'   => (float) Subscription::where('status', 'trial')->sum('amount'),
        ]);
    }

    public function features()
    {
        // Aggregate every distinct feature across plans and which plans include it.
        $plans = Plan::orderBy('price')->get();
        $matrix = collect();
        foreach ($plans as $plan) {
            foreach ((array) $plan->features as $feature) {
                $matrix->push(['feature' => $feature, 'plan' => $plan->name]);
            }
        }
        $features = $matrix->groupBy('feature')->map(fn ($rows, $f) => (object) [
            'feature' => $f,
            'plans'   => $rows->pluck('plan')->unique()->values()->all(),
        ])->values();

        return view('saas::saas.features', ['plans' => $plans, 'features' => $features]);
    }

    public function settings()
    {
        return view('saas::saas.settings', [
            'mode'           => config('saas.enabled') ? 'SaaS (multi-tenant)' : 'Single company',
            'tenantModel'    => config('tenancy.tenant_model'),
            'centralDomains' => config('tenancy.central_domains', []),
            'planCount'      => Plan::count(),
            'tenantCount'    => Tenant::count(),
        ]);
    }

    public function audit()
    {
        // Platform activity: SaaS-entity log entries from the activity log.
        $logs = \Spatie\Activitylog\Models\Activity::whereIn('subject_type', [
            Tenant::class, Plan::class, Subscription::class,
        ])->latest()->take(100)->get();

        return view('saas::saas.audit', ['logs' => $logs]);
    }

    public function support()
    {
        return view('saas::saas.support', [
            'tenants' => Tenant::with('plan')->where('status', 'active')->latest()->get(),
        ]);
    }

    public function analytics()
    {
        $mrr = (float) Subscription::where('status', 'active')->sum('amount');

        return view('saas::saas.analytics', [
            'byPlan'      => Plan::withCount('tenants')->orderBy('name')->get(),
            'byStatus'    => Tenant::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'mrr'         => $mrr,
            'arr'         => $mrr * 12,
            'bySubStatus' => Subscription::selectRaw('status, count(*) as total, sum(amount) as amount')->groupBy('status')->get(),
            'tenantCount' => Tenant::count(),
        ]);
    }

    /* =====================================================================
     | Helpers
     * ===================================================================== */

    private function cycleEnd(?string $cycle)
    {
        return $this->cycleEndFrom(now(), $cycle);
    }

    private function cycleEndFrom($base, ?string $cycle)
    {
        $base = $base instanceof \Carbon\CarbonInterface ? $base->copy() : now();
        return match ($cycle) {
            'yearly'   => $base->addYear(),
            'lifetime' => null,
            default    => $base->addMonth(),
        };
    }

    /** Record a SaaS platform action in the activity log (best-effort). */
    private function log(string $event, $model, string $description): void
    {
        try {
            activity()->performedOn($model)->causedBy(auth()->user())->event($event)->log($description);
        } catch (\Throwable $th) {
            // logging is best-effort
        }
    }
}
