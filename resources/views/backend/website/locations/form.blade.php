@extends('backend.partials.master')
@section('title', ucfirst($type))
@section('maincontent')
<x-page :title="($item->exists ? 'Edit ' : 'Create ').ucfirst($type)" :breadcrumb="['Website Frontend', ucfirst($type), $item->exists ? 'Edit' : 'Create']">
    @if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="tv-card"><div class="tv-card-body"><form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('website.'.$type.'.update', $item->id) : route('website.'.$type.'.store') }}">
        @csrf @if($item->exists) @method('PUT') @endif
        <div class="row">
            @if($type === 'cities')<div class="form-group col-md-6"><label for="destination_id">Country</label><select class="form-control" name="destination_id" id="destination_id" required><option value="">Select country</option>@foreach($destinations as $destination)<option value="{{ $destination->id }}" @selected(old('destination_id', $item->destination_id) == $destination->id)>{{ $destination->name }}</option>@endforeach</select></div>@endif
            @foreach(['name'=>'Name','slug'=>'URL slug (lowercase)','headline'=>'Headline','meta_title'=>'SEO title'] as $key=>$label)
                <div class="form-group col-md-6"><label for="{{ $key }}">{{ $label }}</label><input id="{{ $key }}" name="{{ $key }}" class="form-control" value="{{ old($key, $item->$key) }}" @required(in_array($key, ['name','slug'])) maxlength="255"></div>
            @endforeach
            @foreach(['description'=>'Short description','about'=>'About this location','meta_description'=>'SEO description'] as $key=>$label)
                <div class="form-group col-md-12"><label for="{{ $key }}">{{ $label }}</label><textarea id="{{ $key }}" name="{{ $key }}" rows="4" class="form-control">{{ old($key, $item->$key) }}</textarea></div>
            @endforeach
            <div class="form-group col-md-6"><label for="image_file">Image (JPG, PNG or WebP, up to 8 MB)</label><input id="image_file" type="file" accept="image/jpeg,image/png,image/webp" name="image_file" class="form-control">@if($item->image)<img src="{{ media_url($item->image) }}" alt="{{ $item->name }}" class="img-fluid mt-2" style="max-height:160px">@endif</div>
            <div class="form-group col-md-6"><label for="sort_order">Display order</label><input type="number" min="0" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $item->sort_order ?? 0) }}" required><input type="hidden" name="active" value="0"><label class="mt-3"><input type="checkbox" name="active" value="1" @checked(old('active', $item->active))> Published</label></div>
        </div><button class="btn btn-primary">Save</button> <a class="btn btn-outline-secondary" href="{{ route('website.'.$type.'.index') }}">Cancel</a>
    </form></div></div>
</x-page>
@endsection
