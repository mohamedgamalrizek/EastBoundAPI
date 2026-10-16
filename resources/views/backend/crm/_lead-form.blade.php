@php $lead = $lead ?? null; @endphp

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control input-style-1" placeholder="Lead name" maxlength="255" required value="{{ old('name', $lead->name ?? '') }}">
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
        <label>Website</label>
        <input type="text" name="website" class="form-control input-style-1" placeholder="e.g. Egypt Tours" maxlength="255" value="{{ old('website', $lead->website ?? '') }}">
        @error('website') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Language</label>
        <select name="language" class="form-control input-style-1 select2">
            <option value="">Not specified</option>
            @foreach(\App\Models\Lead::LANGUAGES as $language)
            <option value="{{ $language }}" @selected(old('language', $lead->language ?? '') === $language)>{{ $language }}</option>
            @endforeach
        </select>
        @error('language') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Country</label>
        <input type="text" name="country" class="form-control input-style-1" placeholder="e.g. Egypt" maxlength="255" value="{{ old('country', $lead->country ?? '') }}">
        @error('country') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Campaign Source</label>
        <select name="campaign_source" class="form-control input-style-1 select2">
            <option value="">Not specified</option>
            @foreach(\App\Models\Lead::CAMPAIGN_SOURCES as $campaignSource)
            <option value="{{ $campaignSource }}" @selected(old('campaign_source', $lead->campaign_source ?? '') === $campaignSource)>{{ $campaignSource }}</option>
            @endforeach
        </select>
        @error('campaign_source') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Estimated Value ({{ currency_symbol() }})</label>
        <input type="number" min="0" step="0.01" name="value" class="form-control input-style-1" placeholder="2500" value="{{ old('value', $lead->value ?? 0) }}">
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
        <h5 class="mt-3">Trip Details</h5>
    </div>
    <div class="form-group col-md-6">
        <label>Subject</label>
        <input type="text" name="subject" class="form-control input-style-1" maxlength="255" placeholder="e.g. Tour packages" value="{{ old('subject', $lead->subject ?? '') }}">
        @error('subject') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Destinations</label>
        <input type="text" name="destinations" class="form-control input-style-1" maxlength="255" placeholder="e.g. Cairo, Luxor" value="{{ old('destinations', $lead->destinations ?? '') }}">
        @error('destinations') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>From Date</label>
        <input type="date" name="from_date" class="form-control input-style-1" value="{{ old('from_date', $lead?->from_date?->format('Y-m-d') ?? '') }}">
        @error('from_date') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>To Date</label>
        <input type="date" name="to_date" class="form-control input-style-1" value="{{ old('to_date', $lead?->to_date?->format('Y-m-d') ?? '') }}">
        @error('to_date') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Duration (Days)</label>
        <input type="number" name="duration" min="0" step="1" class="form-control input-style-1" value="{{ old('duration', $lead->duration ?? '') }}">
        @error('duration') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Adults</label>
        <input type="number" name="adults" min="0" step="1" class="form-control input-style-1" value="{{ old('adults', $lead->adults ?? '') }}">
        @error('adults') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Children</label>
        <input type="number" name="children" min="0" step="1" class="form-control input-style-1" value="{{ old('children', $lead->children ?? '') }}">
        @error('children') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-4">
        <label>Budget</label>
        <input type="text" name="budget" maxlength="100" class="form-control input-style-1" placeholder="e.g. high" value="{{ old('budget', $lead->budget ?? '') }}">
        @error('budget') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-6">
        <label>Created At</label>
        <input type="datetime-local" name="created_at" step="1" class="form-control input-style-1" value="{{ old('created_at', $lead?->created_at?->format('Y-m-d\TH:i:s') ?? '') }}">
        <small class="text-muted">Leave empty to use the current time for a new lead.</small>
        @error('created_at') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-12">
        <label>Customer Comments</label>
        <textarea name="comments" rows="3" maxlength="5000" class="form-control input-style-1" dir="auto">{{ old('comments', $lead->comments ?? '') }}</textarea>
        @error('comments') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
    <div class="form-group col-md-12">
        <label>Notes / Original Submission</label>
        <textarea name="notes" rows="3" class="form-control input-style-1" placeholder="Lead details…">{{ old('notes', $lead->notes ?? '') }}</textarea>
        @error('notes') <p class="pt-2 text-danger">{{ $message }}</p> @enderror
    </div>
</div>
