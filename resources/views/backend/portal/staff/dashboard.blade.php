@extends('backend.partials.master')
@section('title') {{ ___('menus.staff_dashboard') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.staff_dashboard') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.dashboard')]">

    <div class="row">
        <div class="col-md-3 col-6 mb-4"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('menus.my_tasks') }}</div><h3 class="mb-0 tv-fw-700">{{ $myTasks }}</h3></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.due_today') }}</div><h3 class="mb-0 text-danger tv-fw-700">{{ $dueToday }}</h3></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.completed') }}</div><h3 class="mb-0 text-success tv-fw-700">{{ $completed }}</h3></div></div></div>
        <div class="col-md-3 col-6 mb-4"><div class="tv-card"><div class="tv-card-body"><div class="text-muted tv-text-xs">{{ ___('label.present_days') }}</div><h3 class="mb-0 tv-fw-700">{{ $present }}</h3></div></div></div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.recent_tasks') }}</h4></div>
                <div class="tv-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="bg"><tr><th>{{ ___('label.task') }}</th><th>{{ ___('label.project') }}</th><th>{{ ___('label.due') }}</th><th>{{ ___('label.priority') }}</th><th>{{ ___('label.status') }}</th></tr></thead>
                            <tbody>
                                @forelse($recentTasks as $t)
                                    @php
                                        $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Low' ? 'success' : 'warning');
                                        $sc = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
                                    @endphp
                                    <tr>
                                        <td>{{ $t->title }}</td>
                                        <td>{{ $t->project }}</td>
                                        <td>{{ $t->due_date?->format('d M Y') }}</td>
                                        <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                                        <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">{{ ___('label.no_tasks_found') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-page>
@endsection
