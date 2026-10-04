@extends('backend.partials.master')
@section('title')
Add Customer
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('customer.index') }}" class="breadcrumb-link active">Customers</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Add</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">Add Customer</h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('customer.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @include('backend.customer._form')
                        <div class="mt-3">
                            <button type="submit" class="j-td-btn">Save</button>
                            <a href="{{ route('customer.index') }}" class="j-td-btn btn-red">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
