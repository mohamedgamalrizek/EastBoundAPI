@extends('backend.partials.master')
@section('title') System Settings @endsection
@section('maincontent')
<x-page title="System Settings" :breadcrumb="['Super Admin','System Settings']">

    <div class="row">
        <div class="col-lg-7 mb-4"><div class="card">
            <div class="card-header"><h4 class="title-site mb-0">Platform Configuration</h4></div>
            <div class="card-body">
                <table class="table table-responsive-sm mb-0">
                    <tbody>
                        <tr><td class="text-muted">Run mode</td><td><span class="bullet-badge bullet-badge-{{ config('saas.enabled') ? 'success' : 'secondary' }}">{{ $mode }}</span></td></tr>
                        <tr><td class="text-muted">Tenant model</td><td><code>{{ $tenantModel }}</code></td></tr>
                        <tr><td class="text-muted">Central domains</td><td>{{ implode(', ', (array) $centralDomains) }}</td></tr>
                        <tr><td class="text-muted">Total plans</td><td>{{ $planCount }}</td></tr>
                        <tr><td class="text-muted">Total tenants</td><td>{{ $tenantCount }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div></div>
        <div class="col-lg-5 mb-4"><div class="card">
            <div class="card-header"><h4 class="title-site mb-0">Switch Mode</h4></div>
            <div class="card-body">
                <p class="text-muted">FLOW runs from one codebase in two modes. Flip via the CLI:</p>
                <pre class="bg-light p-2 rounded"><code>php artisan saas:mode on    # SaaS (multi-tenant)
php artisan saas:mode off   # single company
php artisan saas:mode       # show current</code></pre>
                <small class="text-muted">When OFF, all <code>/saas/*</code> routes are unregistered and this panel disappears.</small>
            </div>
        </div></div>
    </div>

</x-page>
@endsection
