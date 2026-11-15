@extends('backend.partials.master')
@section('title')
Edit Package
@endsection
@section('maincontent')
<div class="container-fluid dashboard-content">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <x-breadcrumbs :items="['Dashboard', 'Tour Packages', 'Edit']" />
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">Edit Package</h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('package.update', $package->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('backend.package._form', ['package' => $package])
                        <div class="mt-3">
                            <button type="submit" class="j-td-btn">Update</button>
                            <a href="{{ route('package.index') }}" class="j-td-btn btn-red">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
