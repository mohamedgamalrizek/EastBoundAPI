@extends('backend.partials.master')
@section('title') Audit Logs @endsection
@section('maincontent')
<x-page title="Audit Logs" :breadcrumb="['Super Admin','Audit Logs']">

    <x-data-table :headers="['Time','By','Action','Description','Subject']" :order="'[[0,&quot;desc&quot;]]'">
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                <td>{{ optional($log->causer)->name ?? 'System' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $log->event === 'deleted' ? 'danger' : ($log->event === 'created' ? 'success' : 'warning') }}">{{ ucfirst($log->event ?? '—') }}</span></td>
                <td>{{ $log->description }}</td>
                <td><small class="text-muted">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</small></td>
            </tr>
        @endforeach
    </x-data-table>

    @if($logs->isEmpty())
        <div class="text-muted text-center mt-3">No platform activity yet — create or change a tenant/plan/subscription and it will appear here.</div>
    @endif

</x-page>
@endsection
