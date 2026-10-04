{{-- Shared CRM Activity form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $leads, $types, $channels. --}}
@php
    $activityDate = old('activity_date', $item && $item->activity_date ? $item->activity_date->format('Y-m-d') : '');
@endphp

<div class="form-row">

    <div class="form-group col-md-6">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $item->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="lead_id">{{ ___('label.lead') }}</label>
        <select id="lead_id" name="lead_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($leads as $lead)
                <option value="{{ $lead->id }}" @selected(old('lead_id', $item->lead_id ?? '') == $lead->id)>{{ $lead->name }}</option>
            @endforeach
        </select>
        @error('lead_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <input type="hidden" id="customer_name" name="customer_name" value="{{ old('customer_name', $item->customer_name ?? '') }}">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="type">{{ ___('label.type') }} <span class="text-danger">*</span></label>
        <select id="type" name="type" class="form-control input-style-1 select2">
            @foreach($types as $t)
                <option value="{{ $t }}" @selected(old('type', $item->type ?? 'followup') === $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('type') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="subject">{{ ___('label.subject') }} <span class="text-danger">*</span></label>
        <input type="text" id="subject" name="subject" class="form-control input-style-1" placeholder="{{ ___('label.subject') }}" value="{{ old('subject', $item->subject ?? '') }}">
        @error('subject') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="channel">{{ ___('label.channel') }}</label>
        <select id="channel" name="channel" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($channels as $ch)
                <option value="{{ $ch }}" @selected(old('channel', $item->channel ?? '') === $ch)>{{ $ch }}</option>
            @endforeach
        </select>
        @error('channel') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="activity_date">{{ ___('label.activity_date') }}</label>
        <input type="date" id="activity_date" name="activity_date" class="form-control input-style-1" value="{{ $activityDate }}">
        @error('activity_date') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="body">{{ ___('label.body') }}</label>
        <textarea id="body" name="body" rows="4" class="form-control input-style-1" placeholder="{{ ___('label.body') }}">{{ old('body', $item->body ?? '') }}</textarea>
        @error('body') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

@push('scripts')
<script src="{{ asset('backend/js/custom/pages/crm-activity-form.js') }}?v={{ filemtime(public_path('backend/js/custom/pages/crm-activity-form.js')) }}"></script>
@endpush

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('crm.activity.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
