@extends('backend.partials.master')
@section('title') Bank Book @endsection
@section('maincontent')
@include('backend.accounting.partials._book', [
    'heading'  => 'Bank Book',
    'crumb'    => 'Bank Book',
    'inLabel'  => 'Deposit',
    'outLabel' => 'Withdraw',
    'empty'    => 'No account is marked as Bank. Set an account\'s cash type to "bank" in the Chart of Accounts.',
])
@endsection
