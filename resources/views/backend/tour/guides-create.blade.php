@extends('backend.partials.master')
@section('title')
    {{ ___('menus.guides') }} {{ ___('label.create') }}
@endsection
@section('maincontent')
    <div class="container-fluid  dashboard-content">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="page-header">
                    <div class="page-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                        class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('tour.guides') }}"
                                        class="breadcrumb-link active">{{ ___('label.guides') }}</a></li>
                                <li class="breadcrumb-item"><a href=""
                                        class="breadcrumb-link active">{{ ___('label.create') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">

                <div class="tv-card">
                <div class="card-header">
                    <h4 class="title-site">{{ ___('label.create') }} {{ ___('menus.guides') }} </h4>
                </div>
                <div class="tv-card-body">
                        <form action="{{ route('tour.guidesStore') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-6 ">
                                    <label>{{ ___('label.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="{{ ___('placeholder.enter_name') }}"
                                        class="form-control input-style-1" name="name" value="{{ old('name') }}">
                                    @error('name')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label class=" label-style-1" for="phone">{{ ___('label.phone') }}</label> <span
                                        class="text-danger">*</span>
                                    <input type="tel" placeholder="{{ ___('placeholder.enter_phone') }}"
                                        class="form-control input-style-1" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label>{{ ___('label.languages') }} </label>
                                    <textarea name="languages" class="form-control input-style-1" rows="3"
                                        placeholder="{{ ___('placeholder.enter_languages') }}">{{ old('languages') }}</textarea>

                                </div>

                                <div class="form-group col-md-6 ">
                                    <label>{{ ___('label.experience_years') }} </label>
                                    <input type="text" placeholder="{{ ___('placeholder.enter_experience_years') }}"
                                        class="form-control input-style-1" name="experience_years"
                                        value="{{ old('experience_years') }}">
                                </div>

                                <div class="form-group col-md-6 ">
                                    @include('backend.components.image-field', [
                                        'name'    => 'photo',
                                        'label'   => ___('label.photo'),
                                        'current' => null,
                                        'folder'  => 'guides',
                                    ])
                                </div>

                                <div class="form-group col-md-6 ">
                                    <label>{{ ___('label.bio') }}</label>
                                    <textarea name="bio" class="form-control input-style-1" rows="3"
                                        placeholder="{{ ___('label.bio') }}">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Which app login (users table) this guide belongs to — powers the
                                     mobile app's guide role. --}}
                                <div class="form-group col-md-6 ">
                                    <label>{{ ___('label.app_login_user') }}</label>
                                    <select name="user_id" class="form-control input-style-1 select2">
                                        <option value="">{{ ___('label.none') }}</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}" @if (old('user_id') == $u->id) selected @endif>
                                                {{ $u->name }} ({{ $u->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label class=" label-style-1" for="status">{{ ___('label.status') }}</label> <span
                                        class="text-danger">*</span>
                                    <select name="status" class="form-control input-style-1 select2">
                                        <option value="active" @if (old('status') == 'active') selected @endif>
                                            {{ ___('label.active') }}</option>
                                        <option value="inactive" @if (old('status') == 'inactive') selected @endif>
                                            {{ ___('label.inactive') }}</option>
                                        <option value="on_tour" @if (old('status') == 'on_tour') selected @endif>
                                            {{ ___('label.on_tour') }}</option>
                                    </select>
                                    @error('status')
                                        <p class="pt-2 text-danger">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                            <div class="j-create-btns">
                                <div class="drp-btns">
                                    <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                    <a href="{{ route('tour.guides') }}" class="j-td-btn btn-red">
                                        <span>{{ ___('label.cancel') }}</span> </a>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
