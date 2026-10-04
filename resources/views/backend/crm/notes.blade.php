@extends('backend.partials.master')
@section('title') Customer Notes @endsection
@section('maincontent')
<x-page title="Customer Notes" :breadcrumb="['CRM','Customer Notes']">

    <x-data-table :headers="['Date','Customer','Subject','Note']" order="[[0,'desc']]">
        @foreach($activities as $a)
            <tr>
                <td>{{ $a->activity_date?->format('d M Y') ?: '—' }}</td>
                <td><b>{{ $a->customer_name }}</b></td>
                <td><span class="bullet-badge bullet-badge-warning">{{ $a->subject }}</span></td>
                <td>{{ $a->body }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
