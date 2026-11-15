<div class="tv-card mb-3">
    <div class="tv-card-head d-flex justify-content-between align-items-center">
        <h4 class="title-site mb-0"><i class="fa fa-filter mr-2" aria-hidden="true"></i>Filter Leads</h4>
        <span class="badge badge-light">{{ number_format($leads->total()) }} results</span>
    </div>
    <div class="tv-card-body">
        @if($errors->any())
            <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="GET" action="{{ route('crm.leads') }}">
            <div class="row">
                <div class="col-lg-6 form-group">
                    <label for="lead-search">Search leads</label>
                    <input id="lead-search" class="form-control" type="search" name="search" value="{{ old('search', request('search')) }}" placeholder="Name, phone, email, subject, interest or destination" maxlength="255">
                </div>
                @foreach(['stage' => 'Stage', 'source' => 'Source'] as $key => $label)
                    <div class="col-lg-3 col-md-6 form-group">
                        <label for="lead-{{ $key }}">{{ $label }}</label>
                        <select id="lead-{{ $key }}" name="{{ $key }}" class="form-control">
                            <option value="">All {{ strtolower($label) }}s</option>
                            @foreach($filterOptions[$key] as $option)
                                <option value="{{ $option }}" @selected(old($key, request($key)) == $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <details @if(collect($filters)->except(['search', 'stage', 'source', 'sort', 'per_page'])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty() || $errors->any()) open @endif>
                <summary class="mb-3">Advanced filters — attribution, ownership, dates and value</summary>
                <div class="row">
                    @foreach(['language' => 'Language', 'country' => 'Country', 'website' => 'Website', 'campaign_source' => 'Campaign source', 'owner' => 'Owner'] as $key => $label)
                        <div class="col-lg-3 col-md-6 form-group">
                            <label for="lead-{{ $key }}">{{ $label }}</label>
                            <select id="lead-{{ $key }}" name="{{ $key }}" class="form-control">
                                <option value="">All</option>
                                @foreach($filterOptions[$key] as $option)
                                    <option value="{{ $option }}" @selected(old($key, request($key)) == $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                    <div class="col-lg-3 col-md-6 form-group">
                        <label for="lead-assigned">Assigned to</label>
                        <select id="lead-assigned" name="assigned_to" class="form-control">
                            <option value="">Everyone</option>
                            <option value="unassigned" @selected(old('assigned_to', request('assigned_to')) === 'unassigned')>Unassigned</option>
                            @foreach($filterUsers as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to', request('assigned_to')) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach(['created_from' => 'Created from', 'created_to' => 'Created to', 'travel_from' => 'Travel start from', 'travel_to' => 'Travel start to'] as $key => $label)
                        <div class="col-lg-3 col-md-6 form-group">
                            <label for="lead-{{ $key }}">{{ $label }}</label>
                            <input id="lead-{{ $key }}" type="date" name="{{ $key }}" class="form-control" value="{{ old($key, request($key)) }}">
                        </div>
                    @endforeach
                    @foreach(['value_min' => 'Minimum value', 'value_max' => 'Maximum value'] as $key => $label)
                        <div class="col-lg-3 col-md-6 form-group">
                            <label for="lead-{{ $key }}">{{ $label }}</label>
                            <input id="lead-{{ $key }}" type="number" min="0" step="0.01" name="{{ $key }}" class="form-control" value="{{ old($key, request($key)) }}" placeholder="Any">
                        </div>
                    @endforeach
                </div>
            </details>
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 form-group">
                    <label for="lead-sort">Sort by</label>
                    <select id="lead-sort" name="sort" class="form-control">
                        @foreach(['newest' => 'Newest first', 'oldest' => 'Oldest first', 'value_high' => 'Highest value', 'value_low' => 'Lowest value', 'name' => 'Name A–Z'] as $value => $label)
                            <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 form-group">
                    <label for="lead-per-page">Per page</label>
                    <select id="lead-per-page" name="per_page" class="form-control">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-7 form-group d-flex flex-wrap tv-action-gap">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-filter mr-1" aria-hidden="true"></i>Apply filters</button>
                    <a href="{{ route('crm.leads') }}" class="btn btn-outline-secondary">Reset filters</a>
                </div>
            </div>
        </form>
    </div>
</div>
