@extends('backend.partials.master')
@section('title')
    {{ ___('label.category') }} {{ ___('label.list') }}
@endsection
@section('maincontent')
    <div class="container-fluid  dashboard-content">
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="page-header">
                    <div class="page-breadcrumb">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"
                                        class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href=""
                                        class="breadcrumb-link active">{{ ___('label.category') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

                <div class="j-parcel-main j-parcel-res">
                    <div class="tv-card">
                        <div class="tv-card-head">
                            <h4 class="title-site">{{ ___('label.package_category') }} </h4>
                            <x-how-it-works />
                            <a href="{{ route('tour.categoryCreate') }}" class="j-td-btn"> 
                                <img src="{{ asset('backend/icons/icon/plus-white.png') }}" class="jj" alt="no image">
                                <span>{{ ___('menus.add') }}</span>
                            </a>
                        </div>

                        <div class="tv-card-body">
                            <x-data-table :headers="['#', 'Name', 'Slug', 'Description', 'Status', 'Action']" :card="false">

                                @forelse($categories as $key => $category)
                                    @php
                                        $cls = $category->status === 'active' ? 'success' : 'danger';
                                    @endphp

                                    <tr id="row_{{ $category->id }}">

                                        <td><b>{{ $categories->firstItem() + $key }}</b></td>

                                        <td>{{ $category->name }}</td>

                                        <td><code>{{ $category->slug }}</code></td>

                                        <td>{{ \Str::limit($category->description, 80, '...') }}</td>

                                        <td>
                                            <span class="badge badge-{{ $cls }}">
                                                {{ ucfirst($category->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="dropdown">
                                                <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                    <a href="{{ route('tour.categoryEdit', $category->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit"></i></a>

                                                    <a href="{{ route('tour.categoryDelete', $category->id) }}"
                                                        class="btn btn-sm btn-outline-danger" onclick="tryDelete(event)"
                                                        data-remove-id="row_{{ $category->id }}"
                                                        data-title="{{ ___('label.delete') }}"
                                                        data-text="{{ ___('alert.this_action_cannot_be_reversed') }}"
                                                        data-confirm-button-text="{{ ___('label.delete') }}"
                                                        data-cancel-button-text="{{ ___('label.cancel') }}"><i class="fa fa-trash"></i></a>

                                                </div>
                                            </div>
                                        </td>

                                    </tr>

                                @empty
        @endforelse

                            </x-data-table>

                            {{-- pagination --}}
                            {{-- @if ($categories->count())
                                <div class="mt-3">
                                    <x-paginate-show :items="$categories" />
                                </div>
                            @endif --}}

                        </div>
                    </div>
                </div>

            </div>
        </div>
        {{-- @include('backend.todo.to_do_proccesing')
    @include('backend.todo.to_do_completed') --}}
    </div>
@endsection()

@push@endpush
