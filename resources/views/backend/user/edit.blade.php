@extends('backend.partials.master')
@section('title')
{{ ___('label.user') }} {{ ___('label.edit') }}
@endsection
@section('maincontent')
<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{___('menus.user_role')}}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('user.index') }}" class="breadcrumb-link">{{ ___('label.user') }}</a></li>
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
                    <h4 class="title-site"> {{ ___('label.edit_user') }}</h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{route('user.update',$user->id)}}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="id" value="{{$user->id}}">

                        <div class="form-row">

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="name">{{ ___('label.name') }}</label> <span class="text-danger">*</span>
                                <input id="name" type="text" name="name" data-parsley-trigger="change" placeholder="{{ ___('placeholder.enter_name') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('name',$user->name) }}" require>
                                @error('name')
                                <small class="text-danger mt-2">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="email">{{ ___('label.email') }}</label> <span class="text-danger">*</span>
                                <input id="email" type="text" name="email" data-parsley-trigger="change" placeholder="{{ ___('placeholder.enter_email') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('email',$user->email) }}" require>
                                @error('email')
                                <small class="text-danger mt-2">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="phone">{{ ___('label.phone') }}</label> <span class="text-danger">*</span>
                                <input id="phone" type="tel" name="phone" data-parsley-trigger="change" placeholder="{{ ___('placeholder.enter_phone') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('phone',$user->phone) }}" require>
                                @error('phone')
                                <small class="text-danger mt-2">{{ $message }}</small>
                                @enderror
                            </div>


                            <div class="form-group col-md-6">
                                <label class="label-style-1">{{ ___('label.dob') }} <span class="text-danger">*</span></label>
                                <input type="date" id="dob" name="date_of_birth" class="form-control input-style-1 flatpickr" value="{{ old('date_of_birth',$user->date_of_birth) }}" placeholder="{{ ___('placeholder.enter_dob') }}">
                                @error('date_of_birth') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
                            </div>



                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="password">{{ ___('label.password') }} <small class="text-muted">({{ ___('label.leave_blank_to_keep_current') }})</small></label>
                                <input id="password" type="password" name="password" data-parsley-trigger="change" placeholder="{{ ___('placeholder.enter_password') }}" autocomplete="off" class="form-control input-style-1">
                                @error('password')
                                <small class="text-danger mt-2">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="input-select">{{ ___('label.role') }}</label> <span class="text-danger">*</span>
                                <select class="form-control input-style-1 select2" id="input-select" name="role_id" required>
                                    @foreach($roles as $role)
                                    <option value="{{$role->id}}" {{ (old('role_id',$user->role_id) == $role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                <small class="text-danger mt-2">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1">{{ ___('label.gender') }} <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-control input-style-1 select2">
                                    <option></option>

                                    @foreach(App\Enums\Gender::cases() as $gender)
                                    <option value="{{ $gender->value }}" @selected(old('gender',$user->gender->value)==$gender->value)>{{ ___("label.{$gender->name}") }}</option>
                                    @endforeach

                                </select>
                                @error('gender') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="nid_number">{{ ___('label.nid_number') }}</label>
                                <input id="nid_number" type="number" name="nid_number" placeholder="{{ ___('placeholder.enter_nid_number') }}" autocomplete="off" class="form-control input-style-1" value="{{ old('nid_number',$user->nid_number) }}">
                                @error('nid_number') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="label-style-1" for="nid">{{ ___('label.nid') }}<span class="fillable"></span></label>
                                <x-file-uploader name="nid" :label="___('label.nid')" :upload="$user->nid"
                                    accept="image/jpg, image/jpeg, image/png, application/pdf" />
                                @error('nid') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="status">{{ ___('label.status') }}</label> <span class="text-danger">*</span>
                                <select name="status" class="form-control input-style-1 select2">
                                    @foreach(config('site.status.default') as $key => $status)
                                    <option value="{{ $key }}" @selected(old('status', $user->status->value)==$key)>{{ ___('label.'.$status) }}</option>
                                    @endforeach
                                </select>
                                @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="label-style-1" for="image">{{ ___('label.image') }}<span class="fillable"></span></label>
                                <x-file-uploader name="image" :label="___('label.image')" :upload="$user->image"
                                    accept="image/jpg, image/jpeg, image/png, application/pdf" />
                                @error('image') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="address">{{ ___('label.address') }}</label> <span class="text-danger">*</span>
                                <textarea name="address" class="form-control input-style-1" rows="3" placeholder="{{ ___('placeholder.enter_address') }}">{{ old('address',$user->address) }}</textarea>
                                @error('address') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1">{{ ___('label.about') }} </label>
                                <textarea name="about" class="form-control input-style-1" rows="3" placeholder="{{ ___('placeholder.about_me') }}">{{ old('about',$user->about) }}</textarea>
                                @error('about') <span class="pt-2 text-danger">{{ $message }}</span> @enderror
                            </div>




                        </div>
                        <div class="form-row">
                            <div class="j-create-btns ml-1">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                    <a href="{{ route('user.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection()
