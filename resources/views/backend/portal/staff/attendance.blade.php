@extends('backend.partials.master')
@section('title') {{ ___('menus.attendance') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.attendance') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.attendance')]">

    <x-data-table :headers="[___('label.date'), ___('label.check_in'), ___('label.check_out'), ___('label.hours'), ___('label.status')]" order="[[0,'desc']]">
        @foreach($attendance as $a)
            @php $c = $a->status === 'Present' ? 'success' : ($a->status === 'Absent' ? 'danger' : 'warning'); @endphp
            <tr>
                <td>{{ $a->date?->format('d M Y') }}</td>
                <td>{{ $a->check_in ?? '—' }}</td>
                <td>{{ $a->check_out ?? '—' }}</td>
                <td>{{ $a->hours ? $a->hours.'h' : '—' }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $a->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
