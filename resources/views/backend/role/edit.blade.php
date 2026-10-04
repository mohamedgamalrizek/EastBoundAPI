@extends('backend.partials.master')
@section('title')
{{ ___('label.role') }} {{ ___('label.edit') }}
@endsection
@section('maincontent')
<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('menus.user') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('role.index') }}" class="breadcrumb-link">{{ ___('label.role') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.edit') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site"> {{ ___('label.edit_role') }}</h4>
                </div>
                <div class="tv-card-body">
                    <form action="{{ route('role.update', ['id' => $role->id]) }}" method="POST" enctype="multipart/form-data" id="basicform">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $role->id }}">

                        <div class="row">

                            <div class="col-12 col-md-6 form-group">
                                <label class=" label-style-1" for="name">{{ ___('label.name') }}</label>
                                <span class="text-danger">*</span>
                                <input id="name" type="text" name="name" data-parsley-trigger="change" placeholder="{{ ___('placeholder.enter_role_name') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('name', $role->name) }}" require>
                                @error('name') <span class="text-danger mt-2">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12 col-md-6 form-group">
                                <label class=" label-style-1" for="status">{{ ___('label.status') }}</label>
                                <span class="text-danger">*</span>
                                <select name="status" class="form-control input-style-1 select2">
                                    @foreach(config('site.status.default') as $key => $status)

                                    <option value="{{ $key }}" @selected(old('status', @$role->status->value)==$key)>{{ ___('label.'.$status) }}</option>
                                    @endforeach
                                </select>
                                @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12 mt-4">
                                @error('permissions')
                                <small class="text-danger d-block mb-2">{{ $message }}</small>
                                @enderror
                                <table class="table border permission-table">
                                    <thead class="bg">
                                        <tr>
                                            <th>{{ ___('permissions.modules') }}</th>
                                            <th>{{ ___('permissions.permissions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($permissions as $permission)
                                        <tr>
                                            <td>
                                                <input type="checkbox" id="module_{{ $permission->attribute }}" class="module-toggle">
                                                <label for="module_{{ $permission->attribute }}">{{ ___('permissions.' . $permission->attribute) }}</label>
                                            </td>
                                            <td>
                                                <div class="row mt-1">
                                                    @foreach ($permission->keywords as $key => $keyword)
                                                    <div class="col-12 col-md-3">
                                                        <input type="checkbox" id="{{ $keyword }}" class="read common-key" name="permissions[]" value="{{ $keyword }}" @checked(old('permissions') ? in_array($keyword, old('permissions')) : ($role->permissions !== null && in_array($keyword, $role->permissions)))>
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


                        <div class="j-create-btns mt-4 pt-4">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                <a href="{{ route('role.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
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



