{{-- Shared FAQ form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="question">{{ ___('label.question') }} <span class="text-danger">*</span></label>
        <input type="text" id="question" name="question" class="form-control input-style-1" placeholder="{{ ___('label.question') }}" value="{{ old('question', $item->question ?? '') }}">
        @error('question') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="category">{{ ___('label.category') }}</label>
        <input type="text" id="category" name="category" class="form-control input-style-1" placeholder="{{ ___('label.category') }}" value="{{ old('category', $item->category ?? '') }}">
        @error('category') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'active') === $st)>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="answer">{{ ___('label.answer') }}</label>
        <textarea id="answer" name="answer" rows="4" class="form-control input-style-1" placeholder="{{ ___('label.answer') }}">{{ old('answer', $item->answer ?? '') }}</textarea>
        @error('answer') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('cms.faq.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
