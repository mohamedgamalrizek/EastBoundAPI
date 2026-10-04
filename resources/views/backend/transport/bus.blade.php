@extends('backend.partials.master')
@section('title') Bus Booking @endsection
@section('maincontent')
<x-page title="Bus Booking" :breadcrumb="['Transport','Bus']">

    <x-data-table :headers="['ID','Customer','Route','Travel Date','Vehicle','Fare','Status','Action']">
        @foreach($bookings as $b)
            @php $c = in_array($b->status, ['Confirmed','Paid','Completed']) ? 'success' : ($b->status === 'Pending' ? 'warning' : 'info'); @endphp
            <tr>
                <td><b>{{ $b->booking_no }}</b></td>
                <td>{{ $b->customer_name }}</td>
                <td>{{ $b->route }}</td>
                <td>{{ $b->travel_date?->format('d M Y') }}</td>
                <td>{{ $b->vehicle }}</td>
                <td>{{ currency_symbol() }}{{ number_format($b->fare) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $b->status }}</span></td>
                <td>
                    <a href="{{ route('transport.show', $b->id) }}" class="text-primary mr-2" title="View">
                        <i class="fa fa-eye"></i>
                    </a>
                    @if(hasPermission('transport_update'))
                    <a href="{{ route('transport.edit', $b->id) }}" class="text-muted" title="{{ ___('label.edit') }}">
                        <i class="fa fa-edit"></i>
                    </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
