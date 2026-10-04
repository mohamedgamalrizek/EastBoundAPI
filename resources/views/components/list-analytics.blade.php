@props([
    'title'  => 'Overview',
    'stats'  => [],        // ['Label' => 'value', ...]  small KPI tiles
    'donut'  => null,      // ['labels' => [...], 'series' => [...]]  status breakdown
    'trend'  => null,      // ['labels' => [...], 'series' => [['name'=>'', 'data'=>[]]], 'type' => 'area'|'bar']
    'donutTitle' => null,  // caption above the donut
    'trendTitle' => null,  // caption above the trend; defaults to the series name
])

@php
    // Unique suffix so multiple analytics blocks never collide on one page.
    $uid = 'la_' . substr(md5(uniqid('', true)), 0, 8);
@endphp

<div class="card tv-analytics mb-3 mb-md-4">
    <div class="card-body">
        <div class="tv-analytics__head mb-3">
            <h5 class="tv-analytics__title">{{ $title }}</h5>
        </div>

        <div class="row">
            {{-- KPI tiles --}}
            @if(!empty($stats))
                <div class="col-lg-{{ $donut ? 4 : 12 }} col-md-12 mb-4 mb-lg-0">
                    <div class="tv-kpi-grid">
                        @foreach($stats as $label => $value)
                            <div class="tv-kpi">
                                <div class="tv-kpi__value">{{ $value }}</div>
                                <div class="tv-kpi__label">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Donut (breakdown) --}}
            @if($donut)
                <div class="col-lg-{{ $stats ? 4 : 5 }} col-md-6 mb-4 mb-lg-0">
                    <div class="tv-chart-caption">{{ $donutTitle ?? 'Breakdown' }}</div>
                    <div id="{{ $uid }}_donut" class="tv-chart"></div>
                </div>
            @endif

            {{-- Trend (line/area/bar). The caption names the single series, so
                 the chart itself does not carry a one-item legend. --}}
            @if($trend)
                <div class="col-lg-{{ $donut ? ($stats ? 4 : 7) : ($stats ? 8 : 12) }} col-md-6 mb-4 mb-lg-0">
                    <div class="tv-chart-caption">
                        {{ $trendTitle ?? ($trend['series'][0]['name'] ?? 'Trend') }}
                    </div>
                    <div id="{{ $uid }}_trend" class="tv-chart"></div>
                </div>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script src="{{ asset('backend/libs/apexcharts/apexcharts.min.js') }}"></script>
    @endpush
@endonce

@push('scripts')
<script type="application/json" data-la-charts="{{ $uid }}">
{
@if($donut)
"donut": { "labels": @json($donut['labels']), "series": @json($donut['series']) }@if($trend),@endif
@endif
@if($trend)
"trend": { "type": "{{ $trend['type'] ?? 'area' }}", "labels": @json($trend['labels']), "series": @json($trend['series']) }
@endif
}
</script>
@endpush
