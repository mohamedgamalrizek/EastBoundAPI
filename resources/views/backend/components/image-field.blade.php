{{-- Reusable CMS image field: pick a file, or tick "remove" to clear it.

     There used to be a visible "…or paste an image URL / path" text box beside
     the picker. It was confusing (two inputs for one value) so it is now
     hidden: it still carries the stored value so that saving a form without
     re-uploading keeps the current image, and existing remote/CDN URLs (the
     seeded Unsplash links) survive untouched.

     Expects:
       $name    — column name, e.g. "image" (file input) → hidden input is "{name}_url"
       $label   — field label
       $current — existing stored value (URL or path under public/), or null
       $folder  — public/uploads sub-folder, shown as a hint only
--}}
@php
    $textName = $name . '_url';
    $current  = $current ?? null;
    $value    = old($textName, $current);
@endphp

<label class="label-style-1" for="{{ $name }}">{{ $label }}</label>

{{-- File picker + preview come from the shared <x-file-uploader> component. --}}
<x-file-uploader :name="$name" :label="$label" :path="$value" accept="image/*" />
@error($name) <small class="text-danger d-block mb-2">{{ $message }}</small> @enderror

{{-- Keeps the current value across a save that does not re-upload. --}}
<input type="hidden" name="{{ $textName }}" value="{{ $value }}">

@if($value)
    <label class="d-inline-flex align-items-center mb-1 tv-click-choice">
        <input type="checkbox" name="{{ $name }}_remove" value="1" @checked(old($name . '_remove'))>
        <span class="text-muted">Remove this image</span>
    </label>
@endif

<small class="text-muted d-block">
    Uploads are saved to <code>public/uploads/{{ $folder ?? 'cms' }}</code>.
    Uploading a new file replaces the current one.
</small>
@error($textName) <small class="text-danger d-block">{{ $message }}</small> @enderror
