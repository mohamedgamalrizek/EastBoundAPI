@extends('backend.partials.master')
@section('title') {{ ___('menus.my_tasks') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.my_tasks') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.my_tasks')]">

    <x-data-table :headers="[___('label.task'), ___('label.project'), ___('label.assigned_to'), ___('label.due'), ___('label.priority'), ___('label.status')]">
        @foreach($tasks as $t)
            @php
                $pc = $t->priority === 'High' ? 'danger' : ($t->priority === 'Low' ? 'success' : 'warning');
                $sc = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
            @endphp
            <tr>
                <td><b>{{ $t->title }}</b></td>
                <td>{{ $t->project }}</td>
                <td>{{ $t->assignedTo->name ?? '—' }}</td>
                <td>{{ $t->due_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $pc }}">{{ $t->priority }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
