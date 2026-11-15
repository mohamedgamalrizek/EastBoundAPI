@extends('backend.partials.master')
@section('title')
{{ ___('label.user') }} {{ ___('permissions.permissions') }}
@endsection
@section('maincontent')

<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <x-breadcrumbs :items="[___('menus.user_role'), ___('label.user'), ___('permissions.permissions')]" />
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">

        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="tv-card">

                <div class="tv-card-head mb-3">
                    <h4 class="title-site">{{ ___('label.user') }}</h4>
                    <x-how-it-works />
                </div>

                <div class="tv-card-body">
                    <form action="{{route('user.permission.update',['id'=>$user->id])}}" method="POST" enctype="multipart/form-data" id="basicform">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                                <div class="">
                                    <table class="table table-responsive-sm border permission-table w-100">
                                        <thead class="bg">
                                            <tr>
                                                <th>{{ ___('permissions.modules') }}</th>
                                                <th>{{ ___('permissions.permissions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach ($permissions as $permission )
                                            <tr>
                                                <td>{{ ___('permissions.' . $permission->attribute) }}</td>
                                                <td>

                                                    @if($permission->attribute === 'leads')
                                                    <p class="text-muted small">Read/Create/Update/Delete control actions. Without View All, users can access only leads assigned to their account. Assign allows changing ownership. Restore and Force Delete are separate permissions.</p>
                                                @endif
                                                <div class="row mt-1">
                                                        @foreach ($permission->keywords as $key => $keyword)
                                                        <div class="col-12 col-md-3">
                                                            <input type="checkbox" id="{{ $keyword }}" class="read common-key" name="permissions[]" value="{{ $keyword }}" @checked(in_array($keyword, $user->permissions ?? []))>
                                                            <label for="{{ $keyword }}">{{ ___("permissions.{$key}") }}</label>
                                                        </div>
                                                        @endforeach
                                                    </div>

                                                </td>
                                            </tr>
                                            @endforeach


                                        </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="j-create-btns">
                            <div class="drp-btns pt-4 mt-4">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                <a href="{{ route('user.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection()

@push('scripts')
<script src="{{ asset('backend/js/roles/roles.js') }}"></script>
@endpush
