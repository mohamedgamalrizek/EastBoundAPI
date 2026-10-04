<?php

namespace Modules\Saas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

/**
 * The per-tenant application. These actions run INSIDE a tenant's context
 * (the route group in routes/tenant.php initialises tenancy by domain), so
 * every DB query here hits the tenant's OWN database, never the central one.
 *
 * Auth is a lightweight session login against the tenant's `users` table
 * (seeded with the owner at signup).
 */
class TenantController extends Controller
{
    public function home()
    {
        return session('tenant_user')
            ? redirect()->route('tenant.dashboard')
            : redirect()->route('tenant.login');
    }

    public function loginForm()
    {
        if (session('tenant_user')) {
            return redirect()->route('tenant.dashboard');
        }

        return view('saas::tenant.login', ['tenantName' => $this->tenantName()]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = DB::table('users')->where('email', $request->email)->first();

        if ($user && $user->status === 'active' && Hash::check($request->password, $user->password)) {
            session(['tenant_user' => $user->id, 'tenant_user_name' => $user->name, 'tenant_user_role' => $user->role]);

            return redirect()->route('tenant.dashboard');
        }

        return back()->withInput()->with('danger', 'Invalid email or password.');
    }

    public function logout()
    {
        session()->forget(['tenant_user', 'tenant_user_name', 'tenant_user_role']);

        return redirect()->route('tenant.login');
    }

    public function dashboard()
    {
        return view('saas::tenant.dashboard', [
            'tenantName' => $this->tenantName(),
            'customers'  => DB::table('customers')->count(),
            'packages'   => DB::table('packages')->count(),
            'bookings'   => DB::table('bookings')->count(),
            'revenue'    => (float) DB::table('bookings')->sum('amount'),
            'recent'     => DB::table('bookings')->latest('id')->take(8)->get(),
        ]);
    }

    public function customers()
    {
        return view('saas::tenant.customers', [
            'tenantName' => $this->tenantName(),
            'rows'       => DB::table('customers')->orderByDesc('id')->get(),
        ]);
    }

    public function packages()
    {
        return view('saas::tenant.packages', [
            'tenantName' => $this->tenantName(),
            'rows'       => DB::table('packages')->orderByDesc('id')->get(),
        ]);
    }

    public function bookings()
    {
        return view('saas::tenant.bookings', [
            'tenantName' => $this->tenantName(),
            'rows'       => DB::table('bookings')->orderByDesc('id')->get(),
        ]);
    }

    /** The current tenant's display name (from the central tenant record). */
    private function tenantName(): string
    {
        return optional(tenant())->name ?? 'My Workspace';
    }
}
