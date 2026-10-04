@php $customer = $customer ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control input-style-1" placeholder="e.g. Ayesha Rahman"
            value="{{ old('name', $customer->name ?? '') }}">
        @error('name') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control input-style-1" placeholder="you@email.com"
            value="{{ old('email', $customer->email ?? '') }}">
        @error('email') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control input-style-1" placeholder="+880 17..."
            value="{{ old('phone', $customer->phone ?? '') }}">
        @error('phone') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Address</label>
        <input type="text" name="address" class="form-control input-style-1" placeholder="City, Country"
            value="{{ old('address', $customer->address ?? '') }}">
        @error('address') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Avatar</label>
        <x-file-uploader name="customerAvatar" label="Avatar" :path="$customer?->avatar" accept="image/*" />
        @error('avatar') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Tier <span class="text-danger">*</span></label>
        @php $selTier = old('tier', $customer->tier ?? 'Silver'); @endphp
        <select name="tier" class="form-control input-style-1 select2">
            @foreach(['Silver','Gold','Platinum'] as $tier)
            <option value="{{ $tier }}" @selected($selTier === $tier)>{{ $tier }}</option>
            @endforeach
        </select>
        @error('tier') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-6">
        <label>Status <span class="text-danger">*</span></label>
        @php $selStatus = old('status', $customer->status ?? 'active'); @endphp
        <select name="status" class="form-control input-style-1 select2">
            <option value="active" @selected($selStatus === 'active')>Active</option>
            <option value="inactive" @selected($selStatus === 'inactive')>Inactive</option>
        </select>
        @error('status') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>

    <div class="form-group col-md-12">
        <label>Notes</label>
        <textarea name="notes" rows="3" class="form-control input-style-1" placeholder="Internal notes…">{{ old('notes', $customer->notes ?? '') }}</textarea>
        @error('notes') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
</div>
