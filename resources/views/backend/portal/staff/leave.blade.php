@extends('backend.partials.master')
@section('title') {{ ___('menus.leave_requests') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.leave_requests') }}" :breadcrumb="[___('permissions.staff_portal'), ___('label.leave')]">

    <x-slot name="action">
        <a href="{{ route('staff.leave.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.apply_leave') }}</span>
        </a>
    </x-slot>

    <x-data-table :headers="[___('label.type'), ___('label.from'), ___('label.to'), ___('label.days'), ___('label.reason'), ___('label.status')]">
        @foreach($leaves as $l)
            @php $c = $l->status === 'Approved' ? 'success' : ($l->status === 'Rejected' ? 'danger' : 'warning'); @endphp
            <tr>
                <td><b>{{ $l->leave_type }}</b></td>
                <td>{{ $l->from_date?->format('d M Y') }}</td>
                <td>{{ $l->to_date?->format('d M Y') }}</td>
                <td>{{ $l->days }}</td>
                <td>{{ $l->reason ?? '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $l->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
