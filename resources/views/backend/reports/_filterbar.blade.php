{{-- Shared report filter bar: date range + Filter / Clear / Export CSV.
     Expects $filters (['from'=>.., 'to'=>..]) from the controller. --}}
@php $f = $filters ?? []; @endphp
<div class="tv-card mb-3 mb-md-4">
    <div class="tv-card-body">
        <form method="GET" class="form-row align-items-end">
            <div class="form-group col-md-3 mb-2">
                <label class="label-style-1" for="from">{{ ___('label.from_date') }}</label>
                <input type="date" id="from" name="from" value="{{ $f['from'] ?? '' }}" class="form-control input-style-1">
            </div>
            <div class="form-group col-md-3 mb-2">
                <label class="label-style-1" for="to">{{ ___('label.to_date') }}</label>
                <input type="date" id="to" name="to" value="{{ $f['to'] ?? '' }}" class="form-control input-style-1">
            </div>
            <div class="form-group col-md-6 mb-2">
                <button type="submit" class="j-td-btn"><i class="fa fa-filter"></i> {{ ___('label.filter') }}</button>
                <a href="{{ url()->current() }}" class="j-td-btn btn-red"><i class="fa fa-eraser"></i> {{ ___('label.clear') }}</a>
                <a href="{{ url()->current() }}?{{ http_build_query(array_merge($f, ['export' => 'csv'])) }}" class="j-td-btn"><i class="fa fa-download"></i> {{ ___('label.export_csv') }}</a>
            </div>
        </form>
    </div>
</div>
