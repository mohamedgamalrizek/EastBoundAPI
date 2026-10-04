@extends('backend.partials.master')

@section('title', ___('label.add') . ' | ' . ___('menus.faq') )

@section('maincontent')
<div class="container-fluid  dashboard-content">

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link  ">{{ ___('label.faq') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.add') }}</a></li>
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
                    <h4 class="title-site">{{ ___('label.add') }} {{ ___('menus.faq') }} </h4>
                </div>
                <div class="tv-card-body">

                    <form action="{{ route('cms.faq.store') }}" method="post">
                        @csrf

                        <div class="form-row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ ___('label.question') }} <span class="text-danger">*</span></label>
                                    <input type="text" placeholder="{{ ___('placeholder.enter_question') }}" class="form-control input-style-1" name="question" value="{{ old('question') }}">
                                    @error('question') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ ___('label.answer') }} <span class="text-danger">*</span></label>
                                    <textarea name="answer" class="form-control input-style-1" rows="6" placeholder="{{ ___('placeholder.enter_answer') }}">{{ old('answer') }}</textarea>
                                    @error('answer') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ ___('label.order') }} </label>
                                    <input type="number" placeholder="{{ ___('placeholder.enter_order') }}" class="form-control input-style-1" name="order" value="{{ old('order') }}">
                                    @error('order') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
                                </div>

                                <div class="form-group w-100">
                                    <label for="name">{{ ___('label.type') }}</label> <span class="text-danger">*</span>
                                    <select name="type" class="form-control input-style-1 select2">
                                        <option></option>
                                        @foreach ( App\Enums\FaqType::cases() as $case)
                                        <option value="{{$case->value }}" @selected(old('case')==$case->value)>{{ $case->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
                                </div>

                                <div class="form-group w-100">
                                    <label for="status">{{ ___('label.status') }}</label>
                                    <select name="status" id="status" class="form-control input-style-1 select2">
                                        @foreach ( App\Enums\Status::cases() as $case)
                                        <option value="{{$case->value }}" @selected(old('case', App\Enums\Status::ACTIVE->value)==$case->value)>{{ $case->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                                </div>
                            </div>

                        </div>

                        <div class="drp-btns">
                            <button type="submit" class="j-td-btn">{{ ___('label.save') }}</button>
                            <a href="{{ route('cms.faq.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection



{{-- @pushOnce('scripts')

<script src="{{ asset('backend/js/custom/submit_form.js') }}"></script>

@endPushOnce --}}
