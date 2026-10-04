@extends('backend.partials.master')
@section('title',___('language.title') )

@section('maincontent')

<!-- wrapper  -->
<div class="container-fluid  dashboard-content">
    <!-- pageheader -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}" class="breadcrumb-link">{{ ___('label.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{route('language.index')}}" class="breadcrumb-link">{{___('language.title')}}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.list') }}</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">

            <div class="tv-card">
                <div class="tv-card-head mb-3">
                    <h4 class="title-site">{{ ___('language.language_list') }}
                    </h4>
                    <x-how-it-works />
                    @if (hasPermission('language_create'))
                    <a href="{{ route('language.create') }}" class="j-td-btn">
                        <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image">
                        <span>{{ ___('label.add') }}</span>
                    </a>
                    @endif
                </div>

                <div class="tv-card-body">
                    @php
                        $langHeaders = [___('label.id'), ___('label.icon'), ___('language.language_name'), ___('label.code'), ___('label.status')];
                        if (hasPermission('language_update') || hasPermission('language_phrase_update') || hasPermission('language_delete')) {
                            $langHeaders[] = ___('label.action');
                        }
                    @endphp
                    <x-data-table :card="false" :headers="$langHeaders">
                                @foreach($languages as $language)
                                <tr id="row_{{ $language->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td><i class="{{ @$language->icon_class }}"></i></td>
                                    <td>{{ @$language->name }}</td>
                                    <td>{{ @$language->code }}</td>
                                    <td>{!! @$language->my_status !!}</td>

                                    @if ( hasPermission('language_update') || hasPermission('language_phrase_update') || hasPermission('language_delete'))
                                    <td>
                                        <div class="d-flex align-items-center flex-wrap tv-action-gap">
                                            @if( hasPermission('language_update') && ($language->code !== 'en'))
                                            <a href="{{ route('language.edit',$language->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit color-muted" aria-hidden="true"></i></a>
                                            @endif

                                            @if( hasPermission('language_phrase_update'))
                                            <a href="{{ route('language.edit.phrase',$language->id) }}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit_phrase') }}"><i class="fa fa-language color-muted"></i></a>
                                            @endif

                                            @if( hasPermission('language_delete') && ($language->code !== 'en'))
                                            <a class="btn btn-sm btn-outline-danger" href="{{ route('language.delete', $language->id) }}" onclick="tryDelete(event)" data-reload="{{true}}" data-remove-id="row_{{ $language->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i><span class="sr-only">{{ ___('label.delete') }}</span></a>
                                            @endif
                                        </div>
                                    </td>
                                    @endif

                                </tr>
                                @endforeach
                    </x-data-table>
                </div>


            </div>
        </div>
    </div>
</div>

@endsection


@pushOnce@endPushOnce
