@extends('backend.partials.master')
@section('title') {{ ___('menus.payslips') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.payslips') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.payslips')]">

    <x-data-table :headers="[___('label.month'), ___('label.basic'), ___('label.allowances'), ___('label.deductions'), ___('label.net_pay'), ___('label.status')]" order="[[0,'desc']]">
        @foreach($payslips as $p)
            @php $c = $p->status === 'Paid' ? 'success' : 'warning'; @endphp
            <tr>
                <td><b>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $p->month)->format('M Y') }}</b></td>
                <td>{{ currency_symbol() }}{{ number_format($p->basic) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->allowances) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->deductions) }}</td>
                <td>{{ currency_symbol() }}{{ number_format($p->net_pay) }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $c }}">{{ $p->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
