@extends('backend.partials.master')
@section('title') {{ ___('label.my_documents') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_documents') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.documents')]">

    <x-data-table :headers="[___('label.document'), ___('label.type'), ___('label.file'), ___('label.uploaded'), ___('label.status')]">
        @foreach($documents as $d)
            @php
                $map = ['Verified'=>'success','Pending'=>'warning','Rejected'=>'danger'];
                $c = $map[$d->status] ?? 'warning';
            @endphp
            <tr>
                <td><b>{{ $d->title }}</b></td>
                <td>{{ $d->type }}</td>
                <td>{{ $d->file_label }}</td>
                <td>{{ $d->uploaded_on?->format('d M Y') }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $d->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
