{{-- Shared Announcement form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $audiences, $statuses. --}}
@php
    $publishedOn = old('published_on', $item && $item->published_on ? $item->published_on->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-12">
        <label class="label-style-1" for="title">{{ ___('label.title') }} <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" class="form-control input-style-1" placeholder="{{ ___('label.title') }}" value="{{ old('title', $item->title ?? '') }}">
        @error('title') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="body">{{ ___('label.body') }}</label>
        <textarea id="body" name="body" rows="4" class="form-control input-style-1" placeholder="{{ ___('label.body') }}">{{ old('body', $item->body ?? '') }}</textarea>
        @error('body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="audience">{{ ___('label.audience') }} <span class="text-danger">*</span></label>
        <select id="audience" name="audience" class="form-control input-style-1 select2">
            @foreach($audiences as $aud)
                <option value="{{ $aud }}" @selected(old('audience', $item->audience ?? 'All') === $aud)>{{ $aud }}</option>
            @endforeach
        </select>
        @error('audience') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="published_on">{{ ___('label.published_on') }}</label>
        <input type="date" id="published_on" name="published_on" class="form-control input-style-1" value="{{ $publishedOn }}">
        @error('published_on') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('support.announcement.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
