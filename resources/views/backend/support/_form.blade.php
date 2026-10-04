{{-- Shared Support Ticket form fields. Wrapped by create.blade.php and edit.blade.php.
     Expects: $item (null on create), $customers, $users, $priorities, $statuses. --}}
<div class="form-row">

    <div class="form-group col-md-4">
        <label class="label-style-1" for="ticket_no">{{ ___('label.ticket_no') }} <span class="text-danger">*</span></label>
        <input type="text" id="ticket_no" name="ticket_no" class="form-control input-style-1" placeholder="{{ ___('label.ticket_no') }}" value="{{ old('ticket_no', $item->ticket_no ?? '') }}">
        @error('ticket_no') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-8">
        <label class="label-style-1" for="subject">{{ ___('label.subject') }} <span class="text-danger">*</span></label>
        <input type="text" id="subject" name="subject" class="form-control input-style-1" placeholder="{{ ___('label.subject') }}" value="{{ old('subject', $item->subject ?? '') }}">
        @error('subject') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_name">{{ ___('label.customer_name') }} <span class="text-danger">*</span></label>
        <input type="text" id="customer_name" name="customer_name" class="form-control input-style-1" placeholder="{{ ___('label.customer_name') }}" value="{{ old('customer_name', $item->customer_name ?? '') }}">
        @error('customer_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="customer_id">{{ ___('label.customer') }}</label>
        <select id="customer_id" name="customer_id" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $item->customer_id ?? '') == $customer->id)>{{ $customer->name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="assigned_to">{{ ___('label.assigned_to') }}</label>
        <select id="assigned_to" name="assigned_to" class="form-control input-style-1 select2">
            <option value="">{{ ___('label.select') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('assigned_to', $item->assigned_to ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('assigned_to') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="priority">{{ ___('label.priority') }} <span class="text-danger">*</span></label>
        <select id="priority" name="priority" class="form-control input-style-1 select2">
            @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(old('priority', $item->priority ?? 'Medium') === $p)>{{ $p }}</option>
            @endforeach
        </select>
        @error('priority') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="department">{{ ___('label.department') }}</label>
        <input type="text" id="department" name="department" class="form-control input-style-1" placeholder="{{ ___('label.department') }}" value="{{ old('department', $item->department ?? '') }}">
        @error('department') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">{{ ___('label.status') }} <span class="text-danger">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $item->status ?? 'Open') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $item ? ___('label.save_change') : ___('label.save') }}</button>
        <a href="{{ route('support.index') }}" class="j-td-btn btn-red"><span>{{ ___('label.cancel') }}</span></a>
    </div>
</div>
