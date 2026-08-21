{{--
    x-ui.chart — replacement for MaryUI's `x-mary-chart`.

    Renders a Chart.js chart inside an Alpine component. Two usage modes:

    1. Static props (build the Chart.js config server-side):
         <x-ui.chart type="line" :labels="$labels" :series="$series" :height="256" />
       - type: line | bar | donut  (donut is mapped to Chart.js 'doughnut')
       - labels: string[]
       - series: number[] | number[][]  (one inner array per dataset)
       - height: optional px height for the chart container (default 16rem)

    2. wire:model-bound full Chart.js config array (mirrors MaryUI):
         <x-ui.chart wire:model="myChartConfig" />
       - The bound property is a full Chart.js config ({ type, data, options })
         and is @entangle'd into Alpine so it recreates when the server value changes.

    The chart lifecycle (create in init() via $nextTick, recreate on data change,
    re-init after wire:navigate, destroy on teardown, guard against an undefined
    Chart global and empty data) lives in window.chartComponent (resources/js/charts.js).

    Guards (Requirement 5): if the Chart global is unavailable or there is no data,
    a neutral placeholder is shown instead of a broken canvas.
--}}
@props([
    'type' => 'line',
    'labels' => [],
    'series' => [],
    'height' => null,
])

@once
    @push('head')
        @vite('resources/js/charts.js')
    @endpush
@endonce

@php
    // Detect wire:model binding — when present, the bound property is a full
    // Chart.js config array that we entangle into Alpine (MaryUI-compatible mode).
    $wireModel = $attributes->wire('model');
    $hasWireModel = filled($wireModel->value());

    // Map MaryUI 'donut' to Chart.js 'doughnut'; pass other types through.
    $chartType = $type === 'donut' ? 'doughnut' : $type;

    // Normalize labels/series for the static (non-wire:model) build.
    $labelsArray = is_array($labels) ? array_values($labels) : (array) $labels;
    $seriesArray = is_array($series) ? $series : (array) $series;

    // Accept either a flat number[] (single dataset) or number[][] (multi dataset).
    $isMultiSeries = !empty($seriesArray) && is_array(reset($seriesArray));

    $datasets = [];
    if ($isMultiSeries) {
        foreach (array_values($seriesArray) as $index => $data) {
            $datasets[] = [
                'label' => 'Series ' . ($index + 1),
                'data' => array_values((array) $data),
            ];
        }
    } elseif (!empty($seriesArray)) {
        $datasets[] = [
            'label' => 'Series 1',
            'data' => array_values($seriesArray),
        ];
    }

    // Server-side Chart.js config used when NOT bound via wire:model.
    $chartConfig = [
        'type' => $chartType,
        'data' => [
            'labels' => $labelsArray,
            'datasets' => $datasets,
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ],
    ];

    // Empty-data guard for the static mode (Requirement 5).
    $hasData =
        !empty($labelsArray) &&
        !empty($datasets) &&
        collect($datasets)->contains(fn($dataset) => !empty($dataset['data']));
    $isEmpty = !$hasWireModel && !$hasData;

    // Container height: explicit px prop or a sensible default.
    $heightStyle = $height ? 'height: ' . (int) $height . 'px;' : 'height: 16rem;';
@endphp

@if ($isEmpty)
    {{-- Placeholder shown when there is no data to plot. --}}
    <div {{ $attributes->except(['wire:model', 'wire:model.live'])->class(['flex items-center justify-center rounded-box text-base-content/50']) }}
        style="{{ $heightStyle }}">
        <span class="text-sm">No data available</span>
    </div>
@elseif ($hasWireModel)
    {{-- wire:model mode: entangle the full Chart.js config into Alpine. --}}
    <div wire:ignore x-data="chartComponent({ settings: @entangle($attributes->wire('model')) })"
        {{ $attributes->except(['wire:model', 'wire:model.live'])->class(['relative']) }} style="{{ $heightStyle }}">
        <canvas x-ref="canvas"></canvas>
    </div>
@else
    {{-- Static mode: pass the server-built config to Alpine. --}}
    <div wire:ignore x-data="chartComponent({ settings: {{ \Illuminate\Support\Js::from($chartConfig) }} })" {{ $attributes->class(['relative']) }} style="{{ $heightStyle }}">
        <canvas x-ref="canvas"></canvas>
    </div>
@endif
