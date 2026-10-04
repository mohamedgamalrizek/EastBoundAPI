@extends('backend.partials.master')
@section('title')
Add Package
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
                            <li class="breadcrumb-item"><a href="{{ route('package.index') }}" class="breadcrumb-link active">Tour Packages</a></li>
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
                    <h4 class="title-site">Add Package</h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('package.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @include('backend.package._form')
                        <div class="drp-btns mt-2">
                            <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                            <a href="{{ route('package.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
