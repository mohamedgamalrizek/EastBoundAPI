@extends('backend.partials.master')
@section('title')
{{ ___('menus.category') }} {{ ___('label.create') }}
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
                            <li class="breadcrumb-item"><a href="{{route('tour.category')}}" class="breadcrumb-link active">{{ ___('label.category') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.create') }}</a></li>
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
                    <h4 class="title-site">{{ ___('label.create') }} {{ ___('menus.category') }} </h4>
                </div>
                <div class="tv-card-body">
                    <form action="{{ route('tour.categoryStore') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 ">
                                <label>{{ ___('label.name') }} <span class="text-danger">*</span></label>
                                <input type="text" placeholder="{{ ___('placeholder.enter_name') }}" class="form-control input-style-1" name="name" value="{{ old('name') }}">
                                @error('name')
                                <p class="pt-2 text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group col-md-6 ">
                                <label class=" label-style-1" for="slug">{{ ___('label.slug') }}</label> <span class="text-danger">*</span>
                                <input type="text" placeholder="{{ ___('placeholder.enter_slug') }}" class="form-control input-style-1" name="slug" value="{{ old('slug') }}">
                                @error('slug')
                                <p class="pt-2 text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                             <div class="form-group col-md-6 ">
                                <label>{{ ___('label.description') }} </label>
                                <textarea name="description" class="form-control input-style-1" rows="3" placeholder="{{ ___('placeholder.enter_description') }}">{{ old('description') }}</textarea>

                            </div>

                            <div class="form-group col-md-6">
                                <label class=" label-style-1" for="status">{{ ___('label.status') }}</label> <span class="text-danger">*</span>
                                <select name="status" class="form-control input-style-1 select2">
                                    <option value="active" @if(old('status')=='active') selected @endif>{{ ___('label.active') }}</option>
                                    <option value="inactive" @if(old('status')=='inactive') selected @endif>{{ ___('label.inactive') }}</option>
                                </select>
                                @error('status')
                                <p class="pt-2 text-danger">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_change') }}</button>
                                <a href="{{ route('tour.category') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
