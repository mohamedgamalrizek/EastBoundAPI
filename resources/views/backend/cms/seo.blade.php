@extends('backend.partials.master')
@section('title') {{ ___('menus.seo_settings') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.seo_settings') }}" :breadcrumb="[___('menus.cms'), ___('menus.seo_settings')]">

    <div class="row">
        <div class="col-lg-8">
            <div class="tv-card">
                <div class="tv-card-body">
                    <form action="{{ route('cms.seo.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="seo_meta_title">{{ ___('label.meta_title') }} <span class="text-danger">*</span></label>
                                <input id="seo_meta_title" name="seo_meta_title" class="form-control input-style-1" value="{{ old('seo_meta_title', settings('seo_meta_title') ?: 'FLOW - Travel Agency, Tours, Visa, Flights & Hotels') }}">
                                @error('seo_meta_title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="seo_meta_description">{{ ___('label.meta_description') }} <span class="text-danger">*</span></label>
                                <textarea id="seo_meta_description" name="seo_meta_description" class="form-control input-style-1" rows="3">{{ old('seo_meta_description', settings('seo_meta_description') ?: 'FLOW is a premium travel agency for tour packages, visa processing, flight and hotel booking, Hajj and Umrah.') }}</textarea>
                                @error('seo_meta_description') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-12">
                                <label class="label-style-1" for="seo_meta_keywords">{{ ___('label.meta_keywords') }}</label>
                                <input id="seo_meta_keywords" name="seo_meta_keywords" class="form-control input-style-1" value="{{ old('seo_meta_keywords', settings('seo_meta_keywords') ?: 'travel, tours, visa, umrah, flights') }}">
                                @error('seo_meta_keywords') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="seoOgImage">{{ ___('label.og_image') }}</label>
                                <div class="ot_fileUploader left-side mb-3">
                                    <input class="form-control input-style-1 placeholder" type="text" placeholder="{{ ___('label.og_image') }}" readonly>
                                    <button class="primary-btn-small-input" type="button">
                                        <label class="j-td-btn" for="seoOgImage">{{ ___('label.Browse') }}</label>
                                        <input type="file" name="og_image" id="seoOgImage" class="d-none form-control input-style-1" accept="image/jpg, image/jpeg, image/png, image/webp">
                                    </button>
                                </div>
                                @if(settings('og_image'))
                                    <img src="{{ logo(settings('og_image')) }}" alt="{{ ___('label.og_image') }}" width="160" height="90" class="tv-object-cover">
                                @endif
                                @error('og_image') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label class="label-style-1" for="google_analytics_id">{{ ___('label.google_analytics_id') }}</label>
                                <input id="google_analytics_id" name="google_analytics_id" class="form-control input-style-1" placeholder="G-XXXXXXX" value="{{ old('google_analytics_id', settings('google_analytics_id')) }}">
                                @error('google_analytics_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="j-create-btns">
                            <div class="drp-btns">
                                <button type="submit" class="j-td-btn">{{ ___('label.save_seo') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-page>
@endsection
