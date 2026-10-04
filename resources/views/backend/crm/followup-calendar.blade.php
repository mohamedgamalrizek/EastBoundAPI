@extends('backend.partials.master')
@section('title') Follow-ups @endsection
@section('maincontent')
<x-page title="Follow-ups" :breadcrumb="['CRM','Follow-ups']">

    <x-data-table :headers="['Date','Customer','Subject','Channel','Details']" order="[[0,'asc']]">
        @foreach($activities as $a)
            <tr>
                <td>{{ $a->activity_date?->format('d M Y') ?: '—' }}</td>
                <td><b>{{ $a->customer_name }}</b></td>
                <td>{{ $a->subject }}</td>
                <td>@if($a->channel)<span class="bullet-badge bullet-badge-primary">{{ $a->channel }}</span>@else — @endif</td>
                <td>{{ $a->body }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
