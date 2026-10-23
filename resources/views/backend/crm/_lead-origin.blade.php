@if(hasPermission('crm_read'))
    <div class="form-group col-md-6">
        <label for="bookingLead">Originating lead</label>
        <select id="bookingLead" name="lead_id" class="form-control input-style-1 select2">
            <option value="">-- Unattributed booking --</option>
            @foreach(\App\Models\Lead::orderBy('name')->get(['id', 'name', 'website', 'language']) as $originLead)
                <option value="{{ $originLead->id }}" @selected((string) old('lead_id', $originLeadId ?? '') === (string) $originLead->id)>
                    #{{ $originLead->id }} {{ $originLead->name }} — {{ $originLead->website ?: 'Unspecified' }} / {{ $originLead->language ?: 'Unspecified' }}
                </option>
            @endforeach
        </select>
        @error('lead_id') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
@endif
