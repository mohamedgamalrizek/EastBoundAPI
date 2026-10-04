@extends('backend.partials.master')
@section('title') Assignments @endsection
@section('maincontent')
<x-page title="Assignments" :breadcrumb="['Tasks','Assignments']">

    <x-data-table :headers="['Assigned To','Task','Project','Due','Priority','Status']" :order="'[[0,&quot;asc&quot;]]'">
        @foreach($tasks as $t)
            @php
                $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Medium' ? 'warning' : 'info');
                $sc = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
            @endphp
            <tr>
                <td><b>{{ $t->assignedTo->name ?? '—' }}</b></td>
                <td>{{ $t->title }}</td>
                <td>{{ $t->project }}</td>
                <td>{{ $t->due_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
