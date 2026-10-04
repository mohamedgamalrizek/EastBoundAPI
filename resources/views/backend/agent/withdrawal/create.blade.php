@extends('backend.partials.master')
@section('title') Agent Payout @endsection
@section('maincontent')
<x-page title="{{ ___('label.create') }}" :breadcrumb="['Agent','Payouts','Create']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('agent.withdrawal.store') }}" method="POST">
                        @csrf
                        @include('backend.agent.withdrawal._form', ['item' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
