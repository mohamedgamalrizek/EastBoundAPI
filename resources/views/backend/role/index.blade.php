@extends('backend.partials.master')
@section('title')
{{ ___('label.role') }} {{ ___('label.list') }}
@endsection
@section('maincontent')


<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{___('label.user')}}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('role.index') }}" class="breadcrumb-link">{{ ___('label.role') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.list') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="tv-card">

                <div class="tv-card-head mb-3">
                    <h4 class="title-site">{{ ___('label.role') }}</h4>
                    <x-how-it-works />
                    @if(hasPermission('role_create') )
                    <a href="{{route('role.create')}}" class="j-td-btn"> <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt="no image"> <span>{{ ___('label.add') }} </span> </a>
                    @endif

                </div>


                <div class="tv-card-body">
                    @php
                        $roleHeaders = [___('label.id'), ___('label.name'), ___('label.slug'), ___('label.permission'), ___('label.status')];
                        if (hasPermission('role_update') || hasPermission('role_delete')) {
                            $roleHeaders[] = ___('label.action');
                        }
                    @endphp
                    <x-data-table :card="false" :headers="$roleHeaders">
                            @foreach($roles as $role)
                            <tr id="row_{{ $role->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{$role->name}}</td>
                                <td>{{$role->slug}}</td>
                                <td>
                                    @if(!empty($role->permissions) )
                                    <label class="bullet-badge bullet-badge-info">{{ count($role->permissions) }}</label>
                                    @endif
                                </td>
                                <td>{!! $role->my_status !!}</td>
                                @if(hasPermission('role_update') || hasPermission('role_delete') )
                                <td>
                                    <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                        @if(hasPermission('role_update') )
                                        <a href="{{route('role.edit',$role->id)}}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                        @endif

                                        @if( hasPermission('role_delete') )
                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('role.delete', $role->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $role->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa fa-trash"></i></a>
                                        @endif
                                    </div>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                    </x-data-table>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection()
