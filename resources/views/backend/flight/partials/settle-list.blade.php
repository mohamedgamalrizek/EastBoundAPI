{{-- Tickets eligible for the page's action, each with the form that performs it.
     Shared by reissue / cancellation / refund, which differ only in the extra
     field the action needs.
     Expects: $eligible, $action ('Reissued' | 'Cancelled' | 'Refunded'). --}}
@if(hasPermission('flight_update') && $eligible->count())
<div class="row"><div class="col-12"><div class="tv-card">
    <div class="tv-card-head">
        <h4 class="title-site mb-0">{{ ___('label.eligible_tickets') }}</h4>
        <span class="text-muted tv-text-sm">{{ $eligible->count() }}</span>
    </div>
    <div class="tv-card-body"><div class="table-responsive">
        <table class="table table-responsive-sm">
            <thead class="bg"><tr>
                <th>{{ ___('label.pnr') }}</th>
                <th>{{ ___('label.passenger') }}</th>
                <th>{{ ___('label.route') }}</th>
                <th>{{ ___('label.flight_date') }}</th>
                <th>{{ ___('label.fare') }}</th>
                <th>{{ ___('label.status') }}</th>
                <th class="tv-min-w-320">{{ ___('label.action') }}</th>
            </tr></thead>
            <tbody>
                @foreach($eligible as $ticket)
                <tr>
                    <td><b>{{ $ticket->pnr }}</b></td>
                    <td>{{ $ticket->passenger_name }}</td>
                    <td>{{ $ticket->route }}</td>
                    <td>{{ $ticket->flight_date?->format('d M Y') }}</td>
                    <td>{{ currency_symbol() }}{{ number_format($ticket->fare) }}</td>
                    <td><span class="bullet-badge bullet-badge-{{ $ticket->statusTone() }}">{{ $ticket->status }}</span></td>
                    <td>
                        <form action="{{ route('flight.settle', $ticket->id) }}" method="post" class="form-row align-items-center">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $action }}">

                            @if($action === 'Reissued')
                                <div class="col-auto mb-1">
                                    <input type="date" name="flight_date" class="form-control input-style-1 form-control-sm"
                                           value="{{ $ticket->flight_date?->format('Y-m-d') }}"
                                           title="{{ ___('label.new_flight_date') }}">
                                </div>
                            @endif

                            @if($action === 'Refunded')
                                {{-- Capped at the fare: anything not returned is
                                     recorded as the airline's penalty. --}}
                                <div class="col-auto mb-1">
                                    <input type="number" step="0.01" min="0" max="{{ (float) $ticket->fare }}"
                                           name="refund_amount" class="form-control input-style-1 form-control-sm"
                                           placeholder="{{ ___('label.refund_amount') }}"
                                           value="{{ (float) $ticket->fare }}" required>
                                </div>
                                {{-- Where the money went back from — the refund posts against this account. --}}
                                <div class="col-auto mb-1">
                                    <select name="refund_method" class="form-control input-style-1 form-control-sm">
                                        <option value="Cash">Cash</option>
                                        <option value="Bank">Bank</option>
                                    </select>
                                </div>
                            @endif

                            <div class="col mb-1">
                                <input type="text" name="status_note" class="form-control input-style-1 form-control-sm"
                                       placeholder="{{ ___('label.reason_optional') }}">
                            </div>

                            <div class="col-auto mb-1">
                                <button type="submit" class="btn btn-sm btn-primary">{{ $action }}</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
</div></div></div>
@endif
