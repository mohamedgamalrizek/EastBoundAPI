@extends('backend.partials.master')

@section('title',___('menus.login_activity') )

@section('maincontent')
<div class="container-fluid  dashboard-content">

    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="page-header">
                <div class="page-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">{{ ___('menus.login_activity') }}</a></li>
                            <li class="breadcrumb-item"><a href="" class="breadcrumb-link active">{{ ___('label.list') }}</a></li>
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
                        <h4 class="title-site">{{ ___('menus.login_activity') }} </h4>
                        <x-how-it-works />
                    </div>

                    <div class="tv-card-body">
                        <div class="table-responsive">
                            <table class="table table-responsive-sm">
                                <thead class="bg">
                                    <tr>
                                        <th scope="col">{{ ___('label.id') }}</th>
                                        <th scope="col">{{ ___('label.user') }}</th>
                                        <th scope="col">{{ ___('label.activity') }}</th>
                                        <th scope="col">{{ ___('label.ip') }}</th>
                                        <th scope="col">{{ ___('label.browser') }}</th>
                                        <th scope="col">{{ ___('label.os') }}</th>
                                        <th scope="col">{{ ___('label.device') }}</th>
                                        <th scope="col">{{ ___('label.country') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($loginActivities ?? [] as $loginActivity)
                                    <tr>

                                        <td> {{ $loop->iteration }}</td>
                                        <td> {{ @$loginActivity->user->name }}</td>
                                        <td>{{ ___("alert.{$loginActivity->activity}") }}</td>
                                        <td>{{ @$loginActivity->ip }}</td>
                                        <td>{{ @$loginActivity->browser }}</td>
                                        <td>{{ @$loginActivity->os }}</td>
                                        <td>{{ @$loginActivity->device }}</td>
                                        <td>{{ @$loginActivity->country }}</td>

                                    </tr>

                                    @empty
                                    <x-nodata-found :colspan="8" />
                                    @endforelse

                                </tbody>
                            </table>
                        </div>

                        @if(count($loginActivities))
                        <x-paginate-show :items="$loginActivities" />
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection()
