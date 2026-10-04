@extends('backend.partials.master')

@section('title', ___('menus.stay_tuned') )

@section('maincontent')

<div class="container-fluid  dashboard-content">

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('menus.stay_tuned') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.list') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

            <div class="j-parcel-main j-parcel-res">
                <div class="tv-card">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">{{ ___('label.stay_tuned_list') }} </h4>

                        @if (hasPermission('stay_tuned_create'))
                        <a href="{{ route('stayTuned.create') }}" class="j-td-btn"> <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image"> <span>{{ ___('menus.add') }}</span> </a>
                        @endif

                    </div>


                    <div class="tv-card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-sm">
                                <thead class="bg">
                                    <tr>
                                        <th>{{ ___('label.id') }}</th>
                                        <th>{{ ___('label.icon') }}</th>
                                        <th>{{ ___('label.description') }}</th>
                                        <th>{{ ___('label.order') }}</th>
                                        <th>{{ ___('label.status') }}</th>

                                        @if(hasPermission('stay_tuned_update') || hasPermission('stay_tuned_delete') )
                                        <th class="text-center">{{ ___('label.action') }}</th>
                                        @endif

                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($tunes as $key => $tune)
                                    <tr id="row_{{ $tune->id }}">

                                        <td>{{ $loop->iteration }}</td>
                                        <td> <img src="{{ getImage($tune->icon,'original') }}" alt="icon" width="30" class="rounded"> </td>
                                        <td> {{Str::limit($tune->description,200,' ...')}}</td>
                                        <td> {{$tune->order}}</td>
                                        <td>{!! $tune->my_status !!}</td>

                                        @if(hasPermission('stay_tuned_update') || hasPermission('stay_tuned_delete') )
                                        <td class="text-center">
                                            <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                @if(hasPermission('stay_tuned_update') )
                                                <a href="{{route('stayTuned.edit',$tune->id)}}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                                @endif

                                                @if( hasPermission('stay_tuned_delete') )
                                                <a class="btn btn-sm btn-outline-danger" href="{{ route('stayTuned.delete', $tune->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $tune->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa fa-trash"></i></a>
                                                @endif

                                            </div>
                                        </td>
                                        @endif

                                    </tr>

                                    @empty
                                    <x-nodata-found :colspan="6" />
                                    @endforelse

                                </tbody>
                            </table>
                        </div>

                        @if(count($tunes))
                        <x-paginate-show :items="$tunes" />
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection()


@pushOnce@endPushOnce
