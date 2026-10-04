@extends('backend.partials.master')
@section('title') {{ ___('menus.my_profile') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.my_profile') }}" :breadcrumb="[___('permissions.staff_portal'), ___('menus.profile')]">

    <x-slot name="action">
        <a href="{{ route('staff.profile.edit') }}" class="j-td-btn"><i class="fa fa-edit"></i> <span>{{ ___('label.edit') }}</span></a>
    </x-slot>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="tv-card">
                <div class="tv-card-body text-center">
                    <img src="{{ placeholder_image('avatar') }}" class="rounded-circle mb-2" width="96" height="96" alt="avatar">
                    <h5 class="mb-0">{{ $user?->name ?? ___('label.staff_member') }}</h5>
                    <div class="text-muted">{{ $user?->email }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="tv-card">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.profile_information') }}</h4></div>
                <div class="tv-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.name') }}</div><div class="tv-fw-600">{{ $user?->name ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.email') }}</div><div class="tv-fw-600">{{ $user?->email ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.phone') }}</div><div class="tv-fw-600">{{ $user?->phone ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.address') }}</div><div class="tv-fw-600">{{ $user?->address ?? '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.date_of_birth') }}</div><div class="tv-fw-600">{{ $user?->date_of_birth ? \Illuminate\Support\Carbon::parse($user->date_of_birth)->format('d M Y') : '—' }}</div></div>
                        <div class="col-md-6 mb-3"><div class="text-muted tv-text-xs">{{ ___('label.joined') }}</div><div class="tv-fw-600">{{ $user?->created_at?->format('d M Y') ?? '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-page>
@endsection
