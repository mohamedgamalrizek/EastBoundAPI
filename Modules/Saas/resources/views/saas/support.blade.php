@extends('backend.partials.master')
@section('title') SaaS Support @endsection
@section('maincontent')
<x-page title="Tenant Support" :breadcrumb="['Super Admin','Support']">

    <p class="text-muted">Active tenants and their plan — the platform team's support roster.</p>

    <x-data-table :headers="['Tenant','Email','Plan','Status','Since']">
        @foreach($tenants as $t)
            <tr>
                <td><b>{{ $t->name }}</b></td>
                <td>{{ $t->email ?? '—' }}</td>
                <td>{{ optional($t->plan)->name ?? '—' }}</td>
                <td><span class="bullet-badge bullet-badge-success">{{ ucfirst($t->status) }}</span></td>
                <td>{{ $t->created_at?->format('d M Y') }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
