@extends('backend.partials.master')
@section('title') Kanban Board @endsection
@section('maincontent')
<x-page title="Kanban Board" :breadcrumb="['Tasks','Kanban']">

    @php
        $columns = [
            'Todo'        => 'secondary',
            'In Progress' => 'warning',
            'Done'        => 'success',
        ];
        $grouped = $tasks->groupBy('status');
    @endphp

    <div class="row">
        @foreach($columns as $status => $badge)
            @php $colTasks = $grouped->get($status, collect()); @endphp
            <div class="col-md-4">
                <div class="d-flex justify-content-between mb-2">
                    <b>{{ $status }}</b>
                    <span class="bullet-badge bullet-badge-{{ $badge }}">{{ $colTasks->count() }}</span>
                </div>
                @forelse($colTasks as $t)
                    @php $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Medium' ? 'warning' : 'info'); @endphp
                    <div class="tv-card mb-3 mb-md-4">
                        <div class="tv-card-body p-3">
                            <div class="mb-2">{{ $t->title }}</div>
                            <div class="text-muted mb-2 tv-text-2xs">{{ $t->project }}</div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span>
                                <small class="text-muted">{{ $t->assignedTo->name ?? '—' }}</small>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="tv-card"><div class="tv-card-body p-3 text-muted text-center tv-text-sm">No tasks</div></div>
                @endforelse
            </div>
        @endforeach
    </div>

</x-page>
@endsection
