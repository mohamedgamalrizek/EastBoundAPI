@extends('backend.partials.master')
@section('title') Agent Payout @endsection
@section('maincontent')
<x-page title="{{ ___('label.edit') }}" :breadcrumb="['Agent','Payouts','Edit']">
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('agent.withdrawal.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        @include('backend.agent.withdrawal._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-page>
@endsection
