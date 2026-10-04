@php $item = $document ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1">Visa Application <span class="text-danger">*</span></label>
        @if($item)
            <input type="text" class="form-control input-style-1"
                value="{{ $item->visaApplication?->application_no }} — {{ $item->visaApplication?->applicant_name }}"
                disabled>
        @else
            <select name="visa_application_id" class="form-control input-style-1 select2">
                <option value="">Select application</option>
                @foreach($applications as $application)
                    <option value="{{ $application->id }}" @selected(old('visa_application_id') == $application->id)>
                        {{ $application->application_no }} — {{ $application->applicant_name }}
                    </option>
                @endforeach
            </select>
            @error('visa_application_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
        @endif
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="document_type">Document Type <span class="text-danger">*</span></label>
        @php $selectedType = old('document_type', $item?->document_type); @endphp
        <select id="document_type" name="document_type" class="form-control input-style-1 select2">
            <option value="">Select document type</option>
            @foreach($documentTypes as $type)
                <option value="{{ $type }}" @selected($selectedType === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('document_type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="document">
            {{ $item ? 'Replace File' : 'File' }}
            @if(!$item)<span class="text-danger">*</span>@endif
        </label>
        {{-- Same styled picker the rest of the admin uses; a raw file input
             here was the only one left that showed the browser's own
             "Choose File / No file chosen" control. --}}
        {{-- The stored file lives on the private disk (no public URL), so the
             "current file" preview links through the authorised download
             route instead of a direct asset() path. --}}
        <x-file-uploader
            name="document"
            :label="$item ? 'Replace file' : 'Choose a file'"
            :path="$item?->file_path"
            :url="$item ? route('visa.documents.download', $item->id) : null"
            :filename="$item?->file_name"
            accept=".pdf,.jpg,.jpeg,.png" />
        <small class="text-muted">PDF, JPG or PNG; maximum 5 MB.</small>
        @error('document') <small class="text-danger d-block">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="document_status">Status <span class="text-danger">*</span></label>
        <select id="document_status" name="status" class="form-control input-style-1 select2">
            <option value="Submitted" @selected(old('status', $item?->status ?? 'Submitted') === 'Submitted')>Submitted</option>
            <option value="Verified" @selected(old('status', $item?->status) === 'Verified')>Verified</option>
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>
</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? 'Save Changes' : 'Upload Document' }}</button>
        <a href="{{ route('visa.documents') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
