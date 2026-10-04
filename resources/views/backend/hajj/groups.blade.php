@extends('backend.partials.master')
@section('title') {{ ___('label.group_management') }} @endsection
@section('maincontent')
<x-page :title="___('label.group_management')" :breadcrumb="[___('label.hajj_umrah'), ___('label.groups')]">

    {{-- Groups are picked from a list, never retyped: a typo used to create a
         second group nobody noticed. New ones are made here, and a group can
         exist empty while it is being filled. --}}
    @if(hasPermission('hajj_create'))
    <div class="row"><div class="col-12"><div class="tv-card">
        <div class="tv-card-head">
            <h4 class="title-site mb-0">{{ ___('label.create_group') }}</h4>
        </div>
        <div class="tv-card-body">
            <form action="{{ route('hajj.group.store') }}" method="post" class="form-row align-items-end">
                @csrf
                <div class="form-group col-md-3">
                    <label class="label-style-1">{{ ___('label.group_name') }}</label>
                    <input type="text" name="name" class="form-control input-style-1"
                           placeholder="{{ ___('placeholder.enter_group_name') }}"
                           value="{{ old('name') }}" required>
                    @error('name') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-3">
                    <label class="label-style-1">{{ ___('label.group_leader') }}</label>
                    <input type="text" name="leader" class="form-control input-style-1"
                           placeholder="{{ ___('placeholder.enter_group_leader') }}" value="{{ old('leader') }}">
                    @error('leader') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-4">
                    <label class="label-style-1">{{ ___('label.notes') }}</label>
                    <input type="text" name="notes" class="form-control input-style-1" value="{{ old('notes') }}">
                    @error('notes') <small class="text-danger mt-2">{{ $message }}</small> @enderror
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">{{ ___('label.create_group') }}</button>
                </div>
            </form>
        </div>
    </div></div></div>
    @endif

    {{-- Unplaced pilgrims come first: a pilgrim without a group has no bus, no
         hotel block and no leader, so this is the list that needs clearing. --}}
    @if($unassigned->count())
    <div class="row"><div class="col-12"><div class="tv-card">
        <div class="tv-card-head">
            <h4 class="title-site mb-0">{{ ___('label.unassigned_pilgrims') }}</h4>
            <span class="bullet-badge bullet-badge-danger">{{ $unassigned->count() }}</span>
        </div>
        <div class="tv-card-body"><div class="table-responsive">
            <table class="table table-responsive-sm">
                <thead class="bg"><tr>
                    <th>{{ ___('label.id') }}</th>
                    <th>{{ ___('label.pilgrim') }}</th>
                    <th>{{ ___('label.package') }}</th>
                    <th class="tv-min-w-260">{{ ___('label.assign_to_group') }}</th>
                </tr></thead>
                <tbody>
                    @foreach($unassigned as $p)
                    <tr>
                        <td><b>{{ $p->pilgrim_no }}</b></td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->package_title }}</td>
                        <td>
                            @if(hasPermission('hajj_update') && $groupNames->count())
                            <form action="{{ route('hajj.allocate', $p->id) }}" method="post" class="form-row align-items-center">
                                @csrf @method('PUT')
                                <div class="col mb-1">
                                    <select name="group_name" class="form-control input-style-1 form-control-sm" required>
                                        <option value="">{{ ___('label.select_group') }}</option>
                                        @foreach($groupNames as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto mb-1">
                                    <button type="submit" class="btn btn-sm btn-primary">{{ ___('label.assign') }}</button>
                                </div>
                            </form>
                            @elseif(hasPermission('hajj_update'))
                                <span class="text-muted">{{ ___('label.create_a_group_first') }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div></div>
    </div></div></div>
    @endif

    <div class="row">
        @forelse($groups as $name => $members)
        @php $group = $catalogue->firstWhere('name', $name); @endphp
        <div class="col-xl-6" id="group-card-{{ $group?->id }}">
            <div class="tv-card">
                <div class="tv-card-head">
                    <h4 class="title-site mb-0">
                        {{ $name }}
                        @if($group?->leader)
                            <small class="text-muted d-block">{{ ___('label.group_leader') }}: {{ $group->leader }}</small>
                        @endif
                    </h4>
                    <div class="d-flex align-items-center tv-choice-gap">
                        <span class="bullet-badge bullet-badge-info">{{ $members->count() }} {{ ___('label.pilgrims') }}</span>
                        @if(hasPermission('hajj_delete') && $group && $members->isEmpty())
                            {{-- Only an empty group can go: deleting a filled one would
                                 quietly strip people of their bus and hotel block. --}}
                            <a class="btn btn-sm btn-outline-danger" href="{{ route('hajj.group.delete', $group->id) }}"
                               onclick="tryDelete(event)" data-title="{{ ___('label.delete') }}"
                               data-text="{{ ___('alert.this_action_cannot_be_reversed') }}"
                               data-confirm-button-text="{{ ___('label.delete') }}"
                               data-cancel-button-text="{{ ___('label.cancel') }}"
                               data-remove-id="group-card-{{ $group->id }}"><i class="fa fa-trash"></i></a>
                        @endif
                    </div>
                </div>
                <div class="tv-card-body"><div class="table-responsive">
                    @if($members->isEmpty())
                        <p class="text-muted mb-0">{{ ___('label.no_pilgrims_in_group') }}</p>
                    @else
                    <table class="table table-responsive-sm mb-0">
                        <thead class="bg"><tr>
                            <th>{{ ___('label.id') }}</th>
                            <th>{{ ___('label.pilgrim') }}</th>
                            <th>{{ ___('label.status') }}</th>
                            <th>{{ ___('label.move_to') }}</th>
                        </tr></thead>
                        <tbody>
                            @foreach($members as $p)
                            <tr>
                                <td>{{ $p->pilgrim_no }}</td>
                                <td>{{ $p->name }}</td>
                                <td><span class="bullet-badge bullet-badge-{{ $p->paymentTone() }}">{{ $p->payment_status }}</span></td>
                                <td>
                                    @if(hasPermission('hajj_update'))
                                    <form action="{{ route('hajj.allocate', $p->id) }}" method="post" class="form-row align-items-center">
                                        @csrf @method('PUT')
                                        <div class="col mb-1">
                                            <select name="group_name" class="form-control input-style-1 form-control-sm">
                                                <option value="">{{ ___('label.unassigned') }}</option>
                                                @foreach($groupNames as $option)
                                                    <option value="{{ $option }}" @selected($option === $p->group_name)>{{ $option }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-auto mb-1">
                                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-check"></i></button>
                                        </div>
                                    </form>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div></div>
            </div>
        </div>
        @empty
        <div class="col-12"><div class="tv-card"><div class="tv-card-body text-center text-muted py-5">
            {{ ___('alert.no_data_available') }}
        </div></div></div>
        @endforelse
    </div>

</x-page>
@endsection
