@extends('backend.partials.master')
@section('title') {{ ___('menus.agent_support') }} @endsection
@section('maincontent')
<x-page title="{{ ___('menus.agent_support') }}" :breadcrumb="[___('menus.agent_portal'), ___('label.support')]">

    {{-- The agent's half of support: they open the ticket, the desk routes it.
         Department and assignee stay in the admin Support module. --}}
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.raise_a_ticket') }}</h4></div>
                <div class="tv-card-body">
                    <form method="POST" action="{{ route('agent.support.raise') }}">
                        @csrf
                        <div class="form-group">
                            <label class="label-style-1" for="subject">{{ ___('label.subject') }} <span class="text-danger">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control input-style-1"
                                   placeholder="{{ ___('label.what_do_you_need_help_with') }}" value="{{ old('subject') }}">
                            @error('subject') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                        </div>
                        <div class="form-group">
                            <label class="label-style-1" for="priority">{{ ___('label.priority') }}</label>
                            <select id="priority" name="priority" class="form-control input-style-1">
                                @foreach($priorities as $p)
                                    <option value="{{ $p }}" @selected(old('priority', 'Medium') === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('priority') <small class="text-danger mt-2 d-block">{{ $message }}</small> @enderror
                        </div>
                        <button type="submit" class="j-td-btn">{{ ___('label.submit_ticket') }}</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="tv-card h-100">
                <div class="tv-card-head"><h4 class="title-site mb-0">{{ ___('label.my_open_tickets') }}</h4></div>
                <div class="tv-card-body table-responsive">
                    <table class="table table-hover">
                        <thead class="bg"><tr><th>{{ ___('label.ticket') }}</th><th>{{ ___('label.subject') }}</th><th>{{ ___('label.priority') }}</th><th>{{ ___('label.status') }}</th></tr></thead>
                        <tbody>
                            @forelse($tickets->where('status', '!=', 'Closed') as $t)
                                <tr>
                                    <td><b>{{ $t->ticket_no }}</b></td>
                                    <td>{{ $t->subject }}</td>
                                    <td>{{ $t->priority }}</td>
                                    <td><span class="bullet-badge bullet-badge-{{ $t->status === 'Pending' ? 'warning' : 'info' }}">{{ $t->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center">{{ ___('label.nothing_open_right_now') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <x-data-table :headers="[___('label.ticket'), ___('label.subject'), ___('label.customer'), ___('label.priority'), ___('label.department'), ___('label.updated'), ___('label.status')]">
        @foreach($tickets as $t)
            @php $cls = $t->status === 'Closed' ? 'success' : ($t->status === 'Pending' ? 'warning' : 'info'); @endphp
            <tr>
                <td><b>{{ $t->ticket_no }}</b></td>
                <td>{{ $t->subject }}</td>
                <td>{{ $t->customer_name }}</td>
                <td>{{ $t->priority }}</td>
                <td>{{ $t->department }}</td>
                <td>{{ $t->updated_at?->diffForHumans() }}</td>
                <td><span class="bullet-badge bullet-badge-{{ $cls }}">{{ $t->status }}</span></td>
            </tr>
        @endforeach
    </x-data-table>

</x-page>
@endsection
