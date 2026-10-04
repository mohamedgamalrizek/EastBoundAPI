@props(['key' => null])

{{-- Collapsible "How it works" helper. Content lives in config/how-it-works.php,
     looked up by the current route name (most-specific segment wins), or by an
     explicit key: <x-how-it-works key="package" />. Renders nothing when no
     content matches, so it is safe to include on every page. --}}
@php
    $hiwRoute = \Illuminate\Support\Facades\Route::currentRouteName() ?? '';
    $hiwCandidates = $key ? [$key] : [];
    $hiwParts = array_values(array_filter(explode('.', $hiwRoute)));
    for ($i = count($hiwParts); $i >= 1; $i--) {
        $hiwCandidates[] = implode('.', array_slice($hiwParts, 0, $i));
    }
    // Flat lookup: keys like "tour.category" contain dots, so config() dot
    // notation would mis-read them as nesting.
    $hiwAll = config('how-it-works', []);
    $hiw = null;
    foreach ($hiwCandidates as $hiwCandidate) {
        if ($hiwCandidate && isset($hiwAll[$hiwCandidate])) {
            $hiw = $hiwAll[$hiwCandidate];
            break;
        }
    }
    $hiwId = 'hiw-' . substr(md5(($key ?? '') . '|' . $hiwRoute), 0, 10);
@endphp

@if($hiw)
<div class="hiw">
    <button type="button" class="hiw-toggle" data-hiw-target="#{{ $hiwId }}" aria-expanded="false" aria-controls="{{ $hiwId }}">
        <i class="fa fa-question-circle-o" aria-hidden="true"></i>
        <span>How it works</span>
        <i class="fa fa-angle-down hiw-chevron" aria-hidden="true"></i>
    </button>
    <div class="hiw-panel" id="{{ $hiwId }}">
        <div class="hiw-panel-clip">
            <div class="hiw-panel-inner">
                <h6 class="hiw-title"><i class="fa fa-info-circle" aria-hidden="true"></i> {{ $hiw['title'] ?? 'How it works' }}</h6>
                @if(!empty($hiw['intro']))
                <p class="hiw-intro">{{ $hiw['intro'] }}</p>
                @endif
                @if(!empty($hiw['steps']))
                <ol class="hiw-steps">
                    @foreach($hiw['steps'] as $hiwStep)
                    <li>{{ $hiwStep }}</li>
                    @endforeach
                </ol>
                @endif
                @if(!empty($hiw['tips']))
                <div class="hiw-tips">
                    @foreach($hiw['tips'] as $hiwTip)
                    <p><i class="fa fa-lightbulb-o" aria-hidden="true"></i> {{ $hiwTip }}</p>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script src="{{ asset('frontend/js/pages/how-it-works.js') }}?v={{ filemtime(public_path('frontend/js/pages/how-it-works.js')) }}"></script>
@endpush
@endonce
@endif
