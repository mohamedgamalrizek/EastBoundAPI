@extends('backend.partials.master')
@section('title') Communication History @endsection
@section('maincontent')
<x-page title="Communication History" :breadcrumb="['CRM','Communication History']">

    <x-data-table :headers="['Date','Channel','Customer','Subject','Details']" order="[[0,'desc']]">
        @foreach($activities as $a)
            @php
                $map = ['Email'=>'primary','Call'=>'success','SMS'=>'info','WhatsApp'=>'success','Meeting'=>'warning'];
                $cls = $map[$a->channel] ?? 'primary';
            @endphp
            <tr>
                <td>{{ $a->activity_date?->format('d M Y') ?: '—' }}</td>
                <td>@if($a->channel)<span class="bullet-badge bullet-badge-{{ $cls }}">{{ $a->channel }}</span>@else — @endif</td>
                <td><b>{{ $a->customer_name }}</b></td>
                <td>{{ $a->subject }}</td>
                <td>{{ $a->body }}</td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
