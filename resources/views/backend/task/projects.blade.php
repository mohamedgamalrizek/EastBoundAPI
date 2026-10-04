@extends('backend.partials.master')
@section('title') Projects @endsection
@section('maincontent')
<x-page title="Projects" :breadcrumb="['Tasks','Projects']">

    <x-data-table :headers="['Project','Total Tasks','Completed','Progress']">
        @foreach($projects as $p)
            @php
                $pct = $p->total > 0 ? round(($p->done / $p->total) * 100) : 0;
                $c   = $pct >= 80 ? 'success' : ($pct >= 40 ? 'warning' : 'info');
            @endphp
            <tr>
                <td><b>{{ $p->project }}</b></td>
                <td>{{ $p->total }}</td>
                <td>{{ $p->done }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $pct }}% done</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
