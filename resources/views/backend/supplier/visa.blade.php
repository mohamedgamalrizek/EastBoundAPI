@extends('backend.partials.master')
@section('title') Visa Partners @endsection
@section('maincontent')
<x-page title="Visa Partners" :breadcrumb="['Suppliers','Visa Partners']">

    <x-data-table :headers="['Partner','Contact Person','Phone','Email','Balance','Status']">
        @foreach($suppliers as $s)
            @php $c = $s->status === 'active' ? 'success' : 'danger'; @endphp
            <tr>
                <td><b>{{ $s->name }}</b></td>
                <td>{{ $s->contact_person ?? '—' }}</td>
                <td>{{ $s->phone ?? '—' }}</td>
                <td>{{ $s->email ?? '—' }}</td>
                <td>{{ currency_symbol() }}{{ number_format($s->balance) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ ucfirst($s->status) }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
