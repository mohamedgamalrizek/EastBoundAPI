@extends('backend.partials.master')
@section('title') Create Lead @endsection
@section('maincontent')
<x-page title="Create Lead" :breadcrumb="['CRM','Leads','Create']">
    <div class="row"><div class="col-lg-12">
        <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">New Lead</h4>
                </div>
                <div class="tv-card-body">
            <form action="{{ route('crm.leads.store') }}" method="post">
                @csrf
                @include('backend.crm._lead-form')
                <div class="mt-3">
                    <button type="submit" class="j-td-btn">Save Lead</button>
                    <a href="{{ route('crm.leads') }}" class="j-td-btn btn-red">Cancel</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-page>
@endsection
