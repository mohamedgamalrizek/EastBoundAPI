@extends('backend.partials.master')
@section('title')
Edit Customer
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <x-breadcrumbs :items="['Dashboard', 'Customers', 'Edit']" />
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">Edit Customer</h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('customer.update', $customer->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.customer._form', ['customer' => $customer])
                        <div class="mt-3">
                            <button type="submit" class="j-td-btn">Update</button>
                            <a href="{{ route('customer.index') }}" class="j-td-btn btn-red">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
