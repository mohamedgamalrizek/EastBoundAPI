@extends('backend.partials.master')
@section('title') Deadlines @endsection
@section('maincontent')
<x-page title="Deadlines" :breadcrumb="['Tasks','Deadlines']">

    <x-data-table :headers="['Task','Project','Owner','Deadline','Days Left','Status']" :order="'[[3,&quot;asc&quot;]]'">
        @foreach($tasks as $t)
            @php
                $days = now()->startOfDay()->diffInDays($t->due_date, false);
                $sc   = $t->status === 'Done' ? 'success' : ($t->status === 'In Progress' ? 'warning' : 'secondary');
                if ($t->status === 'Done') {
                    $dc = 'success'; $label = 'Done';
                } elseif ($days < 0) {
                    $dc = 'danger'; $label = abs($days) . ' days late';
                } elseif ($days <= 3) {
                    $dc = 'danger'; $label = $days . ' days';
                } elseif ($days <= 7) {
                    $dc = 'warning'; $label = $days . ' days';
                } else {
                    $dc = 'success'; $label = $days . ' days';
                }
            @endphp
            <tr>
                <td><b>{{ $t->title }}</b></td>
                <td>{{ $t->project }}</td>
                <td>{{ $t->assignedTo->name ?? '—' }}</td>
                <td>{{ $t->due_date?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $dc }}">{{ $label }}</span></td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
