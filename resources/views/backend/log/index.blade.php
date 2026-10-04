@extends('backend.partials.master')
@section('title')
{{ ___('label.logs') }} {{ ___('label.list') }}
@endsection
@section('maincontent')
<!-- wrapper  -->
<div class="container-fluid  dashboard-content">
    <!-- pageheader -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.logs') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- end pageheader -->
    <div class="row">
        <!-- data table  -->
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="tv-card">
                <div class="tv-card-body">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">{{ ___('label.logs') }}</h4>
                        <x-how-it-works />
                    </div>
                    <div class="table-responsive">
                        <table class="table table-responsive-sm">
                            <thead class="bg">
                                <tr>
                                    <th>#</th>
                                    <th>{{ ___('label.log_name') }}</th>
                                    <th>{{ ___('label.event') }}</th>
                                    <th>{{ ___('label.subject_type') }}</th>
                                    <th>{{ ___('label.description') }}</th>
                                    <th>{{ ___('label.view') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=1; @endphp
                                @forelse($logs as $log)
                                <tr>
                                    <td>{{$i++}}</td>
                                    <td>{{$log->log_name}}</td>
                                    <td>{{___('label.'.$log->event)}}</td>
                                    <td>{{$log->subject_type}}</td>
                                    <td>{{___('label.'.$log->description)}}</td>
                                    <td>
                                        <a href="" class="btn btn-primary btn-sm modalBtn" data-toggle="modal" data-target="#dynamic-modal" data-modalsize="modal-lg" data-title="{{ ___('label.log_details') }}" data-url="{{ route('activity.logs.view',$log->id) }}"> <i class="fa fa-eye"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <x-nodata-found : colspan="5" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- pagination component -->
                    @if(count($logs))
                    <x-paginate-show :items="$logs" />
                    @endif
                    <!-- pagination component -->
                </div>
            </div>
        </div>
        <!-- end data table  -->
    </div>
</div>
<!-- end wrapper  -->
@endsection()
