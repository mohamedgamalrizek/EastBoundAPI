@extends('backend.partials.master')
@section('title') {{ ___('label.payment_management') }} @endsection
@section('maincontent')
<x-page :title="___('label.payment_management')" :breadcrumb="[___('label.hajj_umrah'), ___('label.payments')]">

    <div class="row">
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.collected') }}</div>
            <h3 class="mb-0 text-success">{{ currency_symbol() }}{{ number_format($collected) }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.outstanding') }}</div>
            <h3 class="mb-0 text-danger">{{ currency_symbol() }}{{ number_format($outstanding) }}</h3>
        </div></div></div>
        <div class="col-md-4 col-6"><div class="tv-card"><div class="tv-card-body">
            <div class="text-muted">{{ ___('label.fully_paid') }}</div>
            <h3 class="mb-0">{{ $fullyPaid }} / {{ $pilgrims->count() }}</h3>
        </div></div></div>
    </div>

    {{-- Pilgrims pay in instalments, so the field takes the amount received now
         and the repository moves it from due to paid. --}}
    <x-data-table :headers="[
        ___('label.id'), ___('label.pilgrim'), ___('label.package'),
        ___('label.paid'), ___('label.outstanding'), ___('label.payment_status'),
        ___('label.record_payment')
    ]">
        @forelse($pilgrims as $p)
            @php $fid = 'hajj-pay-' . $p->id; @endphp
            <tr>
                <td><b>{{ $p->pilgrim_no }}</b></td>
                <td>{{ $p->name }}</td>
                <td>{{ $p->package_title }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->amount_paid) }}</td>
                <td class="{{ (float) $p->amount_due > 0 ? 'text-danger' : 'text-muted' }}">
                    {{ currency_symbol() }}{{ number_format($p->amount_due) }}
                </td>
                <td><span class="bullet-badge bullet-badge-{{ $p->paymentTone() }}">{{ $p->payment_status }}</span></td>
                @if(hasPermission('hajj_update'))
                    <td>
                        <div class="d-flex align-items-center tv-action-gap">
                            <input type="number" step="0.01" min="0" form="{{ $fid }}" name="payment"
                                   class="form-control input-style-1 form-control-sm"
                                   placeholder="{{ ___('label.amount') }}" class="tv-max-w-130">
                            <select form="{{ $fid }}" name="method" class="form-control input-style-1 form-control-sm tv-max-w-130">
                                @foreach(\App\Services\Accounting\BillingService::PAYMENT_METHODS as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                            <button type="submit" form="{{ $fid }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i>
                            </button>
                        </div>
                    </td>
                @else
                    <td>—</td>
                @endif
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">{{ ___('alert.no_data_available') }}</td></tr>
        @endforelse
    </x-data-table>

    @if(hasPermission('hajj_update'))
        @foreach($pilgrims as $p)
            <form id="hajj-pay-{{ $p->id }}" action="{{ route('hajj.allocate', $p->id) }}" method="post" class="d-none">
                @csrf @method('PUT')
            </form>
        @endforeach
    @endif

</x-page>
@endsection
