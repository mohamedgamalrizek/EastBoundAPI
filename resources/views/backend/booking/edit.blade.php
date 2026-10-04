@extends('backend.partials.master')
@section('title')
Edit Booking
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
                            <li class="breadcrumb-item"><a href="{{ route('booking.index') }}" class="breadcrumb-link active">Bookings</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">Edit</a></li>
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
                    <h4 class="title-site">Edit Booking</h4>
                </div>
                <div class="tv-card-body">
                    <form action="{{ route('booking.update', $booking->id) }}" method="post">
                        @csrf
                        @method('PUT')
                        @include('backend.booking._form', ['booking' => $booking])
                        <div class="drp-btns mt-3">
                            <button type="submit" class="j-td-btn">Update</button>
                            <a href="{{ route('booking.index') }}" class="j-td-btn btn-red">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
