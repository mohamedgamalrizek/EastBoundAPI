@extends('backend.partials.master')

@section('title', ___('label.faq_list') )

@section('maincontent')
<div class="container-fluid  dashboard-content">

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('label.faq') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.index') }}</a></li>
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
                    <div class="tv-card-head mb-3">
                        <h4 class="title-site">{{ ___('label.faq_list') }} </h4>

                        @if (hasPermission('cms_create'))
                        <a href="{{ route('cms.faq.create') }}" class="j-td-btn"> <img src="{{ asset('backend') }}/icons/icon//plus-white.png" class="jj" alt="no image"> <span>{{ ___('menus.add') }}</span> </a>
                        @endif

                    </div>


                    <div class="tv-card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-sm">
                                <thead class="bg">
                                    <tr>
                                        <th>{{ ___('label.id') }}</th>
                                        <th>{{ ___('label.question') }}</th>
                                        <th>{{ ___('label.answer') }}</th>
                                        <th>{{ ___('label.order') }}</th>
                                        <th>{{ ___('label.type') }}</th>
                                        <th>{{ ___('label.status') }}</th>

                                        @if(hasPermission('cms_update') || hasPermission('cms_delete') )
                                        <th class="text-center">{{ ___('label.action') }}</th>
                                        @endif

                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse($faqs as $key => $faq)
                                    <tr id="row_{{ $faq->id }}">

                                        <td>{{ $loop->iteration }}</td>
                                        <td> {{$faq->question}}</td>
                                        <td> {{Str::limit($faq->answer,100,' ...')}}</td>
                                        <td> {{$faq->order}}</td>
                                        <td>{!! $faq->type_badge !!}</td>
                                        <td>{!! $faq->my_status !!}</td>

                                        @if(hasPermission('cms_update') || hasPermission('cms_delete') )
                                        <td class="text-center">
                                            <div class="d-flex align-items-center flex-wrap tv-action-gap">

                                                @if(hasPermission('cms_update') )
                                                <a href="{{route('cms.faq.edit',$faq->id)}}" class="btn btn-sm btn-outline-primary" title="{{ ___('label.edit') }}"><i class="fa fa-edit" aria-hidden="true"></i></a>
                                                @endif

                                                @if( hasPermission('cms_delete') )
                                                <a class="btn btn-sm btn-outline-danger" href="{{ route('cms.faq.delete', $faq->id) }}" onclick="tryDelete(event)" data-remove-id="row_{{ $faq->id }}" data-title="{{___('label.delete')}}" data-text="{{___('alert.this_action_cannot_be_reversed')}}" data-confirm-button-text="{{___('label.delete')}}" data-cancel-button-text="{{___('label.cancel')}}"><i class="fa fa-trash"></i></a>
                                                @endif

                                            </div>
                                        </td>
                                        @endif

                                    </tr>

                                    @empty
                                    <x-nodata-found :colspan="7" />
                                    @endforelse

                                </tbody>
                            </table>
                        </div>

                        @if(count($faqs))
                        <x-paginate-show :items="$faqs" />
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection()
