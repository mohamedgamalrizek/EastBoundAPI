@extends('backend.partials.master')
@section('title') Calendar View @endsection
@section('maincontent')
<x-page title="Calendar View" :breadcrumb="['Tasks','Calendar']">

    <x-data-table :headers="['Due Date','Task','Project','Assigned To','Priority','Status']" :order="'[[0,&quot;asc&quot;]]'">
        @foreach($tasks as $t)
            @php
                $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Medium' ? 'warning' : 'info');
                $sc = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
            @endphp
            <tr>
                <td><b>{{ $t->due_date?->format('d M Y') }}</b></td>
                <td>{{ $t->title }}</td>
                <td>{{ $t->project }}</td>
                <td>{{ $t->assignedTo->name ?? '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
