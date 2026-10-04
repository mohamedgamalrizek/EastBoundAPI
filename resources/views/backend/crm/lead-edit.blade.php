@extends('backend.partials.master')
@section('title') Edit Lead @endsection
@section('maincontent')
<x-page title="Edit Lead" :breadcrumb="['CRM','Leads','Edit']">
    <div class="row"><div class="col-lg-12">
        <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">Edit Lead</h4>
                </div>
                <div class="tv-card-body">
            <form action="{{ route('crm.leads.update', $lead->id) }}" method="post">
                @csrf
                @method('PUT')
                @include('backend.crm._lead-form', ['lead' => $lead])
                <div class="mt-3">
                    <button type="submit" class="j-td-btn">Update Lead</button>
                    <a href="{{ route('crm.leads') }}" class="j-td-btn btn-red">Cancel</a>
                </div>
            </form>
        </div></div>
    </div></div>
</x-page>
@endsection
