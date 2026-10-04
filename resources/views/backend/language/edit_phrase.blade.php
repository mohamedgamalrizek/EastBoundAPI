@extends('backend.partials.master')

@section('title', ___('language.edit_phrase') . ' | ' . ___('menus.language'))

@section('maincontent')
<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('language.index')}}" class="breadcrumb-link">{{___('menus.language')}}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('language.edit_phrase') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="tv-card">
                <div class="tv-card-head mb-3">
                    <h4 class="title-site">{{ ___('language.edit_phrase') }} </h4>
                    <x-how-it-works />
                </div>

                <div class="tv-card-body">

                    <form action="{{ route('language.update.phrase') }}" method="post" onsubmit="submitForm(event)">
                        @csrf

                        <input type="hidden" name="code" id="code" value="{{ @$lang->code }}">

                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label for="module" class="form-label">{{ ___('language.module') }}</label>
                                <select class="form-control input-style-1 select2" id="module" name="module" data-url="{{ route('language.module.phrase')}}">
                                    @foreach (config('site.translations') as $key => $translation )
                                    <option value="{{$key}}">{{ ___('language.' . $translation) }}</option>
                                    @endforeach
                                </select>
                                @error('module') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <div class="table-responsive">
                                    <table id="phrases-table" class="table table-responsive-sm ">

                                        <thead class="bg">

                                            <tr>
                                                <th>#</th>
                                                <th>{{ ___('language.phrase') }}</th>
                                                <th>{{ ___('language.translation') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <x-nodata-found colspan='3'></x-nodata-found>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="j-td-btn"><i class="fa fa-save"></i> {{ ___('label.update') }}</button>
                            <a href="{{ route('language.index') }}" class="j-td-btn btn-red"> <span>{{ ___('label.cancel') }}</span> </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection()



@push('scripts')

<script src="{{ asset('backend/js/custom/app_language.js') }}"></script>
<script src="{{ asset('backend/js/custom/submit_form.js') }}"></script>

@endpush
