@extends('backend.partials.master')
@section('title') Knowledge Base @endsection
@section('maincontent')
<x-page title="Knowledge Base" :breadcrumb="['Support','Knowledge Base']">

    <x-data-table :headers="['Title','Category','Excerpt','Views','Status']">
        @foreach($articles as $a)
            @php
                $sc = $a->status === 'published' ? 'success' : 'warning';
            @endphp
            <tr>
                <td><b>{{ $a->title }}</b></td>
                <td><span class="bullet-badge bullet-badge-info">{{ $a->category }}</span></td>
                <td>{{ $a->excerpt ?? '—' }}</td>
                <td>{{ number_format($a->views) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $sc }}">{{ ucfirst($a->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
