@extends('backend.partials.master')
@section('title') {{ ___('label.my_support_tickets') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_support_tickets') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.support')]">

    <x-slot name="action">
        <a href="{{ route('cust.support.create') }}" class="j-td-btn">
            <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt=""> <span>{{ ___('label.new_ticket') }}</span>
        </a>
    </x-slot>

    <x-data-table :headers="[___('label.ticket'), ___('label.subject'), ___('label.department'), ___('label.priority'), ___('label.updated'), ___('label.status')]">
        @foreach($tickets as $t)
            @php
                $map = ['Open'=>'warning','Pending'=>'warning','In Progress'=>'info','Resolved'=>'success','Closed'=>'success'];
                $c = $map[$t->status] ?? 'warning';
            @endphp
            <tr>
                <td><b>{{ $t->ticket_no }}</b></td>
                <td>{{ $t->subject }}</td>
                <td>{{ $t->department }}</td>
                <td>{{ $t->priority }}</td>
                <td>{{ $t->updated_at?->diffForHumans() }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
