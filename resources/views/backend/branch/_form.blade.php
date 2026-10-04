@php $app = $item ?? null; @endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label class="label-style-1" for="name">Branch Name <span class=\"text-danger\">*</span></label>
        <input type="text" id="name" name="name" class="form-control input-style-1" placeholder="Branch Name" value="{{ old('name', $app->name ?? '') }}">
        @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="code">Code <span class=\"text-danger\">*</span></label>
        <input type="text" id="code" name="code" class="form-control input-style-1" placeholder="Code" value="{{ old('code', $app->code ?? '') }}">
        @error('code') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="manager_name">Manager</label>
        <input type="text" id="manager_name" name="manager_name" class="form-control input-style-1" placeholder="Manager" value="{{ old('manager_name', $app->manager_name ?? '') }}">
        @error('manager_name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="phone">Phone</label>
        <input type="text" id="phone" name="phone" class="form-control input-style-1" placeholder="Phone" value="{{ old('phone', $app->phone ?? '') }}">
        @error('phone') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control input-style-1" placeholder="Email" value="{{ old('email', $app->email ?? '') }}">
        @error('email') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-6">
        <label class="label-style-1" for="city">City</label>
        <input type="text" id="city" name="city" class="form-control input-style-1" placeholder="City" value="{{ old('city', $app->city ?? '') }}">
        @error('city') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-12">
        <label class="label-style-1" for="address">Address</label>
        <textarea id="address" name="address" rows="3" class="form-control input-style-1" placeholder="Address">{{ old('address', $app->address ?? '') }}</textarea>
        @error('address') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

    <div class="form-group col-md-4">
        <label class="label-style-1" for="status">Status <span class=\"text-danger\">*</span></label>
        <select id="status" name="status" class="form-control input-style-1 select2">
            @foreach($status_options as $opt)
                <option value="{{ $opt }}" @selected(old('status', $app->status ?? '') == $opt)>{{ $opt }}</option>
            @endforeach
        </select>
        @error('status') <small class="text-danger mt-2">{{ $message }}</small> @enderror
    </div>

</div>

<div class="j-create-btns">
    <div class="drp-btns">
        <button type="submit" class="j-td-btn">{{ $app ? 'Save Changes' : 'Save' }}</button>
        <a href="{{ route('branch.index') }}" class="j-td-btn btn-red"><span>Cancel</span></a>
    </div>
</div>
