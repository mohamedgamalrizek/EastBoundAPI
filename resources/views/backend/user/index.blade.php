@extends('backend.partials.master')
@section('title')
{{ ___('label.user') }} {{ ___('label.list') }}
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
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{___('menus.user_role')}}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link">{{ ___('label.user') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.list') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            @php
                $userActiveFilters = collect(request()->only(['name', 'email', 'phone']))
                    ->filter(fn ($v) => $v !== null && $v !== '')->count();
            @endphp
            <div id="userFilterCard" class="tv-card tv-filter-card @if(!$userActiveFilters) tv-collapsed @endif">
                <div class="tv-card-body">
                    <form action="{{ route('user.index') }}" method="GET">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class=" label-style-1" for="name">{{ ___('label.name') }}</label>
                                <input type="text" id="name" name="name" placeholder="{{ ___('label.user') }} {{ ___('label.name') }}" class="form-control input-style-1" value="{{ request('name') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class=" label-style-1" for="email">{{ ___('label.email') }}</label>
                                <input type="text" id="email" name="email" placeholder="{{ ___('label.user') }} {{ ___('label.email') }}" class="form-control input-style-1" value="{{ request('email') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label class=" label-style-1" for="phone">{{ ___('label.phone')}}</label> <span class="text-danger"></span>
                                <input type="text" id="phone" name="phone" placeholder="{{ ___('label.phone') }}" class="form-control input-style-1" value="{{ request('phone') }}">
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="j-td-btn mr-2"><i class="fa fa-filter "></i> {{ ___('label.filter') }}</button>
                                    <a href="{{ route('user.index') }}" class="j-td-btn btn-red mr-2"><i class="fa fa-eraser"></i> {{ ___('label.clear') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tv-card">
                <div class="tv-card-head mb-3">
                    <h4 class="title-site">{{ ___('label.user') }}
                    </h4>
                    <x-how-it-works />
                    <button type="button" class="tv-filter-toggle @if($userActiveFilters) is-active @endif"
                        data-tv-filter-target="#userFilterCard" aria-expanded="{{ $userActiveFilters ? 'true' : 'false' }}">
                        <i class="fa fa-filter"></i><span>{{ ___('label.filter') }}</span>
                        @if($userActiveFilters)<span class="tv-filter-count">{{ $userActiveFilters }}</span>@endif
                    </button>
                    @if (hasPermission('user_create'))
                    <a href="{{ route('user.create') }}" class="j-td-btn"> <img src="{{ asset('backend/icons/icon//plus-white.png') }}" class="jj" alt="no image"> <span>{{ ___('label.add') }}</span> </a>
                    @endif
                </div>

                <div class="tv-card-body">
                    <div class="table-responsive">
                        <table class="table table-responsive-sm ">
                            <thead class="bg">
                                <tr>
                                    <th>{{ ___('label.id') }}</th>
                                    <th>{{ ___('label.details') }}</th>
                                    <th>{{ ___('label.role') }}</th>
                                    <th>{{ ___('permissions.permissions') }}</th>
                                    <th>{{ ___('label.status') }}</th>
                                    @if( hasPermission('permission_update') || hasPermission('user_update') || hasPermission('user_delete') )
                                    <th>{{ ___('label.actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=1; @endphp
                                @forelse($users as $user)
                                <tr id="row_{{ $user->id }}">
                                    <td>{{$i++}}</td>
                                    <td>
                                        <div class="row">
                                            <div class="pr-3">
                                                <img src="{{ getImage($user->image,'original') }}" alt="user" class="rounded" width="40" height="40">
                                            </div>
                                            <div>
                                                <strong>{{$user->name}}</strong>
                                                <p>{{$user->email}}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{@$user->role->name}}</td>
                                    <td>
                                        @if(!empty($user->permissions) )
                                        <label class="label-style-1 badge badge-primary">{{ count($user->permissions) }}</label>
                                        @endif
                                        @if(empty($user->permissions) )
                                        <label class="label-style-1 badge badge-primary">{{ count($user->permissions) }}</label>
                                        @endif
                                    </td>
                                    <td>{!! @$user->MyStatus !!}</td>
                                    @if( hasPermission('permission_update') || hasPermission('user_update') || hasPermission('user_delete') )
                                    <td>
                                        <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                            @if( hasPermission('permission_update') )
                                            <a href="{{route('user.permission',$user->id)}}" class="btn btn-sm btn-outline-primary" title="{{ ___('permissions.permissions') }}" aria-label="{{ ___('permissions.permissions') }}"><i class="fa fa-key" aria-hidden="true"></i></a>
                                            @endif
                                            @if( hasPermission('user_update') )
                                            <a href="{{route('user.edit',$user->id)}}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}" aria-label="{{ ___('label.edit') }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                            @endif
                                            @if( hasPermission('user_delete') )
                                            @if($user->id != 1 && $user->id != auth()->id())
                                            <a class="btn btn-sm btn-outline-danger" href="{{ route('user.delete', $user->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $user->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa fa-trash"></i></a>
                                            @endif
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
                    @if(count($users))
                    <x-paginate-show :items="$users" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection()
