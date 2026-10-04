@extends('backend.profile.master')
@section('title')
{{ ___('menus.profile') }}
@endsection
@section('content')
<div id="update_profile">
    <div class="settings-form">
        <ul class="j-eml-list">
            <li>
                <h6 class="heading-6">Email</h6>
                <span>{{@$user->email}}
                    @if(@$user->email_verified_at==null)
                    <i class="fa-solid fa-circle-exclamation tv-color-danger"></i>
                    @endif
                </span>

            </li>
            <li>
                <h6 class="heading-6">Phone</h6>
                <span>{{@$user->phone}}</span>
            </li>
            <li>
                <h6 class="heading-6">Address</h6>
                <span>{{@$user->address}}</span>
            </li>
            <li>
                <h6 class="heading-6">{{ ___('label.role') }}</h6>
                <span>{{@$user->role->name}}</span>
            </li>

            <li>
                <h6 class="heading-6">{{ ___('label.status') }}</h6>
                <span>{!! @$user->my_status !!}</span>
            </li>
        </ul>
    </div>
</div>
@endsection()


