{{-- Period filter shared by the accounting reports. Every report page passes
     $from / $to straight back from the repository, so the inputs keep whatever
     the report was actually run for. Pass :accounts to add an account picker
     (used by the Ledger). --}}
@php
    $accounts = $accounts ?? null;
    $selected = $selected ?? null;
@endphp
<div class="tv-card mb-3 mb-md-4">
    <div class="tv-card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-3 mb-2">
                <label class="label-style-1" for="from">{{ ___('label.from_date') }}</label>
                <input type="date" id="from" name="from" value="{{ $from ?? '' }}" class="form-control input-style-1">
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="label-style-1" for="to">{{ ___('label.to_date') }}</label>
                <input type="date" id="to" name="to" value="{{ $to ?? '' }}" class="form-control input-style-1">
            </div>
            @if($accounts)
            <div class="form-group col-md-3 mb-2">
                <label class="label-style-1" for="account_id">{{ ___('label.account') }}</label>
                <select id="account_id" name="account_id" class="form-control input-style-1">
                    <option value="">All accounts</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @selected(($selected?->id ?? null) === $account->id)>
                            {{ $account->code }} — {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group col-md-3 mb-2">
                <button type="submit" class="j-td-btn"><i class="fa fa-filter"></i> {{ ___('label.filter') }}</button>
                <a href="{{ url()->current() }}" class="j-td-btn btn-red"><i class="fa fa-eraser"></i> {{ ___('label.clear') }}</a>
            </div>
        </form>
    </div>
</div>
