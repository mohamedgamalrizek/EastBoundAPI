<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout · {{ $plan->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('saas/css/checkout.css') }}?v={{ filemtime(public_path('saas/css/checkout.css')) }}" rel="stylesheet">
</head>
<body>
<div class="wrap">
    <div class="text-center mb-3"><h3 class="brand">FLOW<span class="brand-dot">.</span></h3></div>

    @if(session('danger'))<div class="alert alert-danger">{{ session('danger') }}</div>@endif
    @if($demo)<div class="alert alert-info">Demo mode — no live gateway credentials are set, so payments simulate a result. Add keys in <code>.env</code> to go live.</div>@endif

    <form action="{{ route('saas.payment.pay') }}" method="POST" class="card shadow-sm border-0"><div class="card-body p-4">
        @csrf
        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div><div class="text-muted fs-sm">You're subscribing to</div><h4 class="mb-0">{{ $plan->name }}</h4></div>
            <h3 class="brand mb-0">৳{{ number_format($plan->price) }}<small class="text-muted fs-sm">/{{ $plan->billing_cycle }}</small></h3>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6"><label>Your name</label><input type="text" name="payer_name" class="form-control" value="{{ old('payer_name') }}">@error('payer_name')<small class="text-danger">{{ $message }}</small>@enderror</div>
            <div class="form-group col-md-6"><label>Email</label><input type="email" name="payer_email" class="form-control" value="{{ old('payer_email') }}">@error('payer_email')<small class="text-danger">{{ $message }}</small>@enderror</div>
        </div>

        <label class="font-weight-bold mt-2">Choose a payment method</label>
        @error('gateway')<small class="text-danger d-block">{{ $message }}</small>@enderror
        @php $byRegion = collect($gateways)->groupBy(fn($g)=>$g->region()); $first = true; @endphp
        @foreach(['Bangladesh','India','International'] as $region)
            @if($byRegion->has($region))
                <div class="reg-label">{{ $region }}</div>
                <div class="row">
                    @foreach($byRegion[$region] as $g)
                        <div class="col-6 col-md-3 mb-2">
                            <label class="mb-0 w-100 gw-label">
                                <input type="radio" name="gateway" value="{{ $g->key() }}" class="gw-radio" @checked($first)>
                                <div class="gw">{{ $g->label() }}</div>
                            </label>
                        </div>
                        @php $first = false; @endphp
                    @endforeach
                </div>
            @endif
        @endforeach

        <button type="submit" class="btn btn-blue btn-lg btn-block mt-4">Pay ৳{{ number_format($plan->price) }} →</button>
    </div></form>
</div>
</body>
</html>
