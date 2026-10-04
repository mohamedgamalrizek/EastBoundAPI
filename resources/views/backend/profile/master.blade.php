@extends('backend.partials.master')
@section('title')
{{ ___('menus.profile') }}
@endsection
@section('maincontent')
<div class="container-fluid  dashboard-content">

    <div class="j-profile-main">
        <div class="row">
            <div class="col-xl-4">
                <div class="row page-titles mx-0">
                    <div class="col-sm-6 p-md-0">
                        <div class="welcome-text wel-flex">
                            <div class="wel-user-pic">
                                <img src="{{ getImage($user->image,'original') }}" class="profile-img" alt="">
                            </div>
                            <div class="wel-user-bio">
                                <h5 class="heading-5">{{@$user->name}}</h5>
                                <p class="mb-0">{{@$user->email}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 p-md-0 justify-content-sm-end mt-2 mt-sm-0 d-flex">
                        @if($user->id == auth()->user()->id)
                        <a href="{{ route('profile.edit') }}" id="edit" class="j-td-btn">{{ ___('label.edit') }}</a>
                        @endif
                    </div>
                </div>
                <div class="j-eml-card">
                    <h5 class="heading-5">Basic Information</h5>
                    <ul class="j-eml-list">
                        <li>
                            <h6 class="heading-6">Name</h6>
                            <span>{{@$user->name}}</span>
                        </li>
                        <li>
                            <h6 class="heading-6">{{ ___('label.date_of_birth') }}</h6>
                            <span>{{ dateFormat(@$user->date_of_birth) }}</span>
                        </li>
                        <li>
                            <h6 class="heading-6">{{ ___('label.gender') }}</h6>
                            <span>{{ ___('common.' . @$user->gender->name)  }}</span>
                        </li>

                    </ul>
                </div>
            </div>

            <div class="col-xl-8" id="update">
                <div class="tv-card card-border profile-pd-0">
                    <div class="profile-tab p-0">
                        <div class="custom-tab-1">
                            <ul class="nav nav-tabs">
                                <li class="nav-item"><a href="{{route('profile')}}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile'?'active':''}}">Contact Info</a> </li>
                                <li class="nav-item"><a href="{{route('profile.edit')}}" class="nav-link py-4 {{ Route::currentRouteName() == 'profile.edit'?'active':''}}">Update Profile</a> </li>
                                <li class="nav-item"><a href="{{route('password.edit')}}" class="nav-link py-4 {{ Route::currentRouteName() == 'password.edit'?'active':''}}">Update Password</a> </li>
                            </ul>
                            <div class="tab-content">

                                @yield('content')

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- end wrapper  -->
@endsection()
