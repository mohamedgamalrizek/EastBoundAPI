@extends('backend.partials.master')
@section('title') {{ ___('label.my_wishlist') }} @endsection
@section('maincontent')
<x-page title="{{ ___('label.my_wishlist') }}" :breadcrumb="[___('permissions.customer_portal'), ___('menus.wishlist')]">

    <x-data-table :headers="[___('label.package'), ___('label.destination'), ___('label.duration'), ___('label.price_per_person'), ___('label.saved_on'), ___('label.action')]">
        @forelse($items as $item)
            @php $p = $item->package; @endphp
            @if($p)
                <tr id="row_{{ $item->id }}">
                    <td><b>{{ $p->title }}</b></td>
                    <td>{{ $p->destination }}</td>
                    <td>{{ $p->duration_days }}D / {{ $p->duration_nights }}N</td>
                    <td>{{ currency_symbol() }}{{ number_format($p->price) }}</td>
                    <td>{{ $item->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="d-flex align-items-center flex-wrap tv-action-gap">
                            <a href="{{ route('front.package', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="{{ ___('label.view') }}"><i class="fa fa-eye"></i></a>
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('cust.wishlist.delete', $item->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $item->id }}" data-title="{{ ___('label.remove_from_wishlist_confirm') }}" data-text="{{ ___('alert.this_action_cannot_be_reversed') }}" data-confirm-button-text="{{ ___('label.delete') }}" data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
            @endif
        @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ ___('label.no_saved_packages') }}</td></tr>
        @endforelse
    </x-data-table>

</x-page>
@endsection

@push@endpush
