@extends('backend.partials.master')
@section('title') Activity Timeline @endsection
@section('maincontent')
<x-page title="Activity Timeline" :breadcrumb="['CRM','Activity Timeline']">

    <x-data-table :headers="['Date','Customer','Activity','Channel','Details']" order="[[0,'desc']]">
        @foreach($activities as $a)
            <tr>
                <td>{{ $a->activity_date?->format('d M Y') ?: '—' }}</td>
                <td><b>{{ $a->customer_name }}</b></td>
                <td>{{ $a->subject }}</td>
                <td>@if($a->channel)<span class="bullet-badge bullet-badge-info">{{ $a->channel }}</span>@else — @endif</td>
                <td>{{ $a->body }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
