@extends('backend.partials.master')
@section('title') Cash Book @endsection
@section('maincontent')
{{-- Cash Book and Bank Book share one layout but never share rows: each shows
     only the accounts flagged with its own cash type. --}}
@include('backend.accounting.partials._book', [
    'heading'  => 'Cash Book',
    'crumb'    => 'Cash Book',
    'inLabel'  => 'Receipt',
    'outLabel' => 'Payment',
    'empty'    => 'No account is marked as Cash. Set an account\'s cash type to "cash" in the Chart of Accounts.',
])
@endsection
