@extends('backend.partials.master')
@section('title') Payment Gateways @endsection
@section('maincontent')
<x-page title="Payment Gateways" :breadcrumb="['Super Admin','Payment Gateways']">

    <p class="text-muted">Twelve gateways, each integrated against the provider's official API (no SDK packages). Set credentials in <code>.env</code> to switch one from demo to live.</p>

    @foreach($regions as $region => $gateways)
        <h5 class="title-site mt-3 mb-2">{{ $region }}</h5>
        <div class="row">
            @foreach($gateways as $g)
                <div class="col-md-3 col-6">
                    <div class="card h-100"><div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <b>{{ $g->label() }}</b>
                            @if($g->isConfigured())
                                <span class="bullet-badge bullet-badge-success">Live</span>
                            @else
                                <span class="bullet-badge bullet-badge-secondary">Demo</span>
                            @endif
                        </div>
                        <div class="text-muted mt-1 tv-text-xs">{{ implode(', ', $g->currencies()) }}</div>
                    </div></div>
                </div>
            @endforeach
        </div>
    @endforeach

</x-page>
@endsection
