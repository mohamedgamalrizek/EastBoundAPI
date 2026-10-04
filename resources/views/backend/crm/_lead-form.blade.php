@php $lead = $lead ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control input-style-1" placeholder="Lead name" value="{{ old('name', $lead->name ?? '') }}">
        @error('name') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control input-style-1" placeholder="+880 17..." value="{{ old('phone', $lead->phone ?? '') }}">
        @error('phone') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Email</label>
        <input type="email" name="email" class="form-control input-style-1" placeholder="you@email.com" value="{{ old('email', $lead->email ?? '') }}">
        @error('email') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Interested In</label>
        <input type="text" name="interest" class="form-control input-style-1" placeholder="e.g. Maldives Tour" value="{{ old('interest', $lead->interest ?? '') }}">
        @error('interest') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Source <span class="text-danger">*</span></label>
        @php $selSource = old('source', $lead->source ?? 'Website'); @endphp
        <select name="source" class="form-control input-style-1 select2">
            @foreach(['Facebook','Website','Referral','WhatsApp','Instagram','Walk-in'] as $s)
            <option value="{{ $s }}" @selected($selSource === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('source') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Estimated Value ({{ currency_symbol() }})</label>
        <input type="number" step="0.01" name="value" class="form-control input-style-1" placeholder="115000" value="{{ old('value', $lead->value ?? 0) }}">
        @error('value') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Stage <span class="text-danger">*</span></label>
        @php $selStage = old('stage', $lead->stage ?? 'New'); @endphp
        <select name="stage" class="form-control input-style-1 select2">
            @foreach(['New','Contacted','Proposal','Negotiation','Won','Lost'] as $st)
            <option value="{{ $st }}" @selected($selStage === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('stage') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="owner">Assign To (Owner)</label>
        @php
            $selOwner = old('owner', $lead->owner ?? '');
            // Legacy leads may hold a free-text owner that no longer matches a
            // user account; keep it selectable so editing doesn't drop it.
            $ownerIsKnown = $selOwner === '' || $users->contains('name', $selOwner);
        @endphp
        <select id="owner" name="owner" class="form-control input-style-1 select2" placeholder="Assign To (Owner)">
            <option value=""></option>
            @unless($ownerIsKnown)
            <option value="{{ $selOwner }}" selected>{{ $selOwner }}</option>
            @endunless
            @foreach($users as $user)
            <option value="{{ $user->name }}" @selected($selOwner === $user->name)>{{ $user->name }}</option>
            @endforeach
        </select>
        @error('owner') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-12">
        <label>Notes</label>
        <textarea name="notes" rows="3" class="form-control input-style-1" placeholder="Lead details…">{{ old('notes', $lead->notes ?? '') }}</textarea>
        @error('notes') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
</div>
