@extends('backend.partials.master')
@section('title')
{{ ___('label.to_do_list') }} {{ ___('label.list') }}
@endsection
@section('maincontent')
<div class="container-fluid  dashboard-content">
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.to_do_list') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

            {{-- Filter bar — mirrors the Users list so both pages behave the same.
                 Collapsed by default; the funnel button in the list head opens it.
                 It starts open when a filter is already applied. --}}
            @php
                $todoActiveFilters = collect(request()->only(['title', 'user_id', 'status', 'date_from', 'date_to']))
                    ->filter(fn ($v) => $v !== null && $v !== '')->count();
            @endphp
            <div id="todoFilterCard" class="tv-card mb-3 tv-filter-card @if(!$todoActiveFilters) tv-collapsed @endif">
                <div class="tv-card-body">
                    <form action="{{ route('todo.index') }}" method="GET">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="title">{{ ___('label.title') }}</label>
                                <input type="text" id="title" name="title" class="form-control input-style-1"
                                    placeholder="{{ ___('placeholder.enter_title') }}" value="{{ request('title') }}">
                            </div>

                            <div class="form-group col-md-3">
                                <label class="label-style-1" for="user_id">{{ ___('label.user') }}</label>
                                <select name="user_id" id="user_id" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.all') }}</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label class="label-style-1" for="status">{{ ___('label.status') }}</label>
                                <select name="status" id="status" class="form-control input-style-1 select2">
                                    <option value="">{{ ___('label.all') }}</option>
                                    @foreach(config('site.status.Todo') as $key => $status)
                                    <option value="{{ $key }}" @selected(request('status') !== null && request('status') !== '' && (string) request('status') === (string) $key)>{{ ___("label.$status") }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-2">
                                <label class="label-style-1" for="date_from">{{ ___('label.date') }} ({{ ___('label.from') }})</label>
                                <input type="date" id="date_from" name="date_from" class="form-control input-style-1" value="{{ request('date_from') }}">
                            </div>

                            <div class="form-group col-md-2">
                                <label class="label-style-1" for="date_to">{{ ___('label.date') }} ({{ ___('label.to') }})</label>
                                <input type="date" id="date_to" name="date_to" class="form-control input-style-1" value="{{ request('date_to') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="j-td-btn mr-2"><i class="fa fa-filter"></i> {{ ___('label.filter') }}</button>
                                    <a href="{{ route('todo.index') }}" class="j-td-btn btn-red mr-2"><i class="fa fa-eraser"></i> {{ ___('label.clear') }}</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="j-parcel-main j-parcel-res">
                <div class="tv-card">
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">{{ ___('label.to_do_list') }} </h4>
                        <x-how-it-works />
                        <button type="button" class="tv-filter-toggle @if($todoActiveFilters) is-active @endif"
                            data-tv-filter-target="#todoFilterCard" aria-expanded="{{ $todoActiveFilters ? 'true' : 'false' }}">
                            <i class="fa fa-filter"></i><span>{{ ___('label.filter') }}</span>
                            @if($todoActiveFilters)<span class="tv-filter-count">{{ $todoActiveFilters }}</span>@endif
                        </button>
                        @if (hasPermission('todo_create'))
                        <a href="{{ route('todo.create') }}" class="j-td-btn"> <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image"> <span>{{ ___('menus.add') }}</span> </a>
                        @endif
                    </div>


                    <div class="tv-card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-sm">
                                <thead class="bg">
                                    <tr>
                                        <th>{{ ___('label.id') }}</th>
                                        <th>{{ ___('label.date') }}</th>
                                        <th>{{ ___('label.title') }}</th>
                                        <th>{{ ___('label.description') }}</th>
                                        <th>{{ ___('label.file') }}</th>
                                        <th>{{ ___('label.note') }}</th>
                                        <th>{{ ___('label.status') }}</th>
                                        <th>{{ ___('label.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($all_todo as $key => $todo)
                                    <tr id="row_{{ $todo->id }}">
                                        <td>{{++$key}}</td>
                                        <td>{{ $todo->date }}</td>
                                        <td> {{$todo->title}}</td>
                                        <td> {{\Str::limit($todo->description,100,' ...')}}</td>
                                        <td>
                                            {{-- Guard on ->original too: without it a todo with no file
                                                 rendered a "Download" link pointing at the site root. --}}
                                            @if($todo->upload?->original)
                                                @if($todo->upload->type === 'image')
                                                <a href="{{ asset($todo->upload->original) }}" target="_blank" rel="noopener">
                                                    <img src="{{ asset($todo->upload->image_one ?? $todo->upload->original) }}" alt="" class="tv-img-48 tv-object-cover tv-img-radius-6">
                                                </a>
                                                @else
                                                <a href="{{ asset($todo->upload->original) }}" download>{{ ___('label.download') }}</a>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td> {{$todo->note}}</td>
                                        <td>{!! $todo->TodoStatus !!}</td>
                                        <td>
                                            <div class="input-group">
                                                <div class="input-group-prepend be-addon">

                                                    <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                        @if(hasPermission('todo_update') )
                                                        <a href="{{ route('todo.edit',$todo->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                                        @endif

                                                        @if(hasPermission('todo_delete') )
                                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('todo.delete', $todo->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $todo->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa fa-trash"></i></a>
                                                        @endif

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <x-nodata-found :colspan="8" />
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if(count($all_todo))
                        <x-paginate-show :items="$all_todo" />
                        @endif
                        <!-- pagination component -->
                    </div>
                </div>
            </div>

        </div>
    </div>
    {{-- @include('backend.todo.to_do_proccesing')
    @include('backend.todo.to_do_completed') --}}
</div>

@endsection()
