@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_customers') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_customers') }}" :breadcrumb="[___('menus.agent_portal'), ___('permissions.customers')]">

    <x-data-table :headers="[___('label.customer'), ___('label.email'), ___('label.phone'), ___('label.tier'), ___('label.status')]">
        @foreach($customers as $c)
            <tr>
                <td><b>{{ $c->name }}</b></td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->phone }}</td>
                <td>{!! $c->tierBadge() !!}</td>
                <td>{!! $c->statusBadge() !!}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
