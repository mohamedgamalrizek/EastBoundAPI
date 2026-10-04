@props([
    'name',                 // file input name (and id)
    'label' => null,        // placeholder text shown when nothing is attached
    'upload' => null,       // existing App\Models\Upload (user, todo…)
    'path' => null,         // or a plain stored path (package, customer…)
    'filename' => null,     // display name, when the stored file is a uuid
    'accept' => 'image/jpg, image/jpeg, image/png',
    // For a file on a private disk (no public URL to build from $path): the
    // authorised download route to link/preview through instead. When set,
    // $path is used only to detect "a file exists" and to guess the file
    // icon — never turned into a direct asset() URL.
    'url' => null,
])

@php
    // On edit forms the field used to look empty even when a file was already
    // saved. Prefill the readonly box with the stored file name and show a
    // thumbnail, so "no new file picked" is visibly different from "no file".
    // Two storage shapes exist in this app: an Upload row, or a plain path.
    $currentPath = $upload?->original ?: $path;

    // Seed data stores some images as remote URLs — asset() would prefix the
    // site host onto them and break the link, so pass those through untouched.
    $isRemote = $currentPath && preg_match('#^(https?:)?//#i', $currentPath);
    $bare     = $currentPath ? strtok($currentPath, '?') : null;   // drop any querystring
    // Uploads stored under a generated name (visa documents) pass the original
    // filename in, so the box shows "passport.pdf" rather than a uuid.
    $currentName = $filename ?: ($bare ? basename($bare) : null);
    // Remote image URLs often carry no file extension (CDN links), so treat
    // them as images rather than falling back to the generic file icon.
    $isImage  = $bare && ($isRemote || preg_match('/\.(jpe?g|png|webp|gif|svg)$/i', $bare));

    $currentUrl = $url
        ?: ($upload
            ? getImage($upload, 'original')
            : ($currentPath ? ($isRemote ? $currentPath : asset($currentPath)) : null));

    // A private-disk file has no directly loadable URL to preview as an
    // <img>, even though it's an image — the download route always returns
    // Content-Disposition: attachment, not an inline preview.
    if ($url) {
        $isImage = false;
    }
@endphp

<div class="ot_fileUploader left-side mb-2">
    <input class="form-control input-style-1 placeholder" type="text"
        placeholder="{{ $label ?? ___('label.image') }}"
        value="{{ $currentName }}" readonly>
    <button class="primary-btn-small-input" type="button">
        <label class="j-td-btn" for="{{ $name }}">{{ ___('label.Browse') }}</label>
        <input type="file" class="d-none form-control" name="{{ $name }}" id="{{ $name }}" accept="{{ $accept }}">
    </button>
</div>

@if($currentPath)
    <div class="d-flex align-items-center gap-2 mb-3">
        @if($isImage)
            <img src="{{ $currentUrl }}" alt="{{ $currentName }}"
                 height="44" width="44" class="rounded tv-object-cover">
        @else
            <i class="fa fa-file-alt fa-2x text-muted"></i>
        @endif
        <small class="text-muted">
            {{ ___('label.current') }}:
            <a href="{{ $currentUrl }}" target="_blank" rel="noopener">{{ $currentName }}</a>
            — {{ ___('label.leave_blank_to_keep_current') }}
        </small>
    </div>
@endif
