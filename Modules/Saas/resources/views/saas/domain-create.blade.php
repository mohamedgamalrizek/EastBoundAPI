@extends('backend.partials.master')
@section('title') Add Domain @endsection
@section('maincontent')
<x-page title="Add Domain" :breadcrumb="['Super Admin','Domains','Add']">
    <div class="row"><div class="col-lg-8"><div class="card"><div class="card-body">
        <form action="{{ route('saas.domain.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="tenant_id">Tenant <span class="text-danger">*</span></label>
                    <select id="tenant_id" name="tenant_id" class="form-control input-style-1 select2">
                        <option value="">— Select —</option>
                        @foreach($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected(old('tenant_id') == $tenant->id)>{{ $tenant->name }}</option>
                        @endforeach
                    </select>
                    @error('tenant_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label class="label-style-1" for="domain">Domain <span class="text-danger">*</span></label>
                    <input type="text" id="domain" name="domain" class="form-control input-style-1" value="{{ old('domain') }}" placeholder="company.flow.test">
                    @error('domain') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
            </div>
            <div class="j-create-btns"><div class="drp-btns">
                <button type="submit" class="j-td-btn">Save</button>
                <a href="{{ route('saas.domains') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
            </div></div>
        </form>
    </div></div></div></div>
</x-page>
@endsection
