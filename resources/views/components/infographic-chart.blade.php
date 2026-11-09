@props(['chart', 'variant' => 0])
@php
    $values = $chart['values'] ?? [];
    $maximum = max(array_map('abs', $values ?: [0])) ?: 1;
    $unit = $chart['unit'] ?? '';
    $single = count($values) === 1;
    $number = fn ($value) => \App\Services\InfographicPresentationService::number($value);
    $layout = app(\App\Services\InfographicChartLayout::class)->build($chart, (int) $variant);
    $colors = ['#0f766e', '#2563eb', '#d97706', '#7c3aed', '#e11d48', '#0891b2', '#65a30d', '#c026d3', '#475569', '#ea580c'];
@endphp
<article x-data="{ expanded: false, mode: @js($layout['default']) }" class="min-w-0 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <h3 class="text-base font-bold leading-6 text-slate-900">{{ $chart['title'] }}</h3>
        <div class="flex flex-wrap gap-2 text-xs font-semibold">
            @if ($unit)<span class="rounded-md bg-brand-50 px-2 py-1 text-brand-800">Satuan: {{ $unit }}</span>@endif
            @if (!empty($chart['years']))<span class="rounded-md bg-slate-100 px-2 py-1 text-slate-600">{{ implode(', ', $chart['years']) }}</span>@endif
        </div>
    </div>
    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $chart['description'] ?? 'Angka sesuai data terbit pada rincian berikut.' }}</p>
    @if (!empty($chart['definition']))<p class="mt-2 text-xs leading-6 text-slate-500">{{ $chart['definition'] }}</p>@endif
    @if (!empty($chart['caution']))
        <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">{{ $chart['caution'] }}</p>
    @endif
    @if ($single)
        <div class="mt-5 rounded-xl bg-brand-50 p-5">
            <p class="break-words text-3xl font-bold tracking-tight text-brand-900">{{ $number($values[0]) }} <span class="text-base font-semibold">{{ $unit }}</span></p>
            <p class="mt-2 text-sm text-brand-800">{{ $chart['labels'][0] }}</p>
        </div>
    @else
        <label class="mt-4 flex flex-wrap items-center justify-between gap-2 text-xs font-semibold text-slate-600">
            Tampilan grafik
            <select x-model="mode" class="rounded-lg border-slate-300 bg-white text-xs text-slate-700">
                @foreach ($layout['options'] as $type => $label)<option value="{{ $type }}">{{ $label }}</option>@endforeach
            </select>
        </label>
        @include('filament.partials.infographic-visualization')
        <div x-show="mode === 'bar'" x-cloak>
        <div class="mt-5 space-y-5" role="list" aria-label="{{ $chart['title'] }}">
            @foreach ($values as $index => $value)
                <div role="listitem" @if ($index >= 8) x-show="expanded" x-cloak @endif>
                    <div class="mb-2 flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 text-sm">
                        <span class="min-w-0 break-words font-medium text-slate-700">{{ $chart['labels'][$index] }}</span>
                        <span class="font-bold tabular-nums text-slate-900">{{ $number($value) }} <span class="font-normal text-slate-500">{{ $unit }}</span></span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true"><div class="h-full rounded-full bg-brand-600" style="width: {{ min(100, abs($value) / $maximum * 100) }}%"></div></div>
                </div>
            @endforeach
        </div>
        @if (count($values) > 8)
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-slate-500" x-text="expanded ? 'Semua {{ count($values) }} rincian ditampilkan' : 'Menampilkan 8 dari {{ count($values) }} rincian'">Menampilkan 8 dari {{ count($values) }} rincian</p>
                <button type="button" x-on:click="expanded = !expanded" x-bind:aria-expanded="expanded" class="rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-800" x-text="expanded ? 'Ringkas tampilan' : 'Tampilkan semua rincian'">Tampilkan semua rincian</button>
            </div>
        @endif
        <p class="mt-4 text-xs leading-5 text-slate-500">Panjang batang menunjukkan perbandingan besaran, bukan persentase dari total. @if (min($values ?: [0]) < 0)Batang memakai besar absolut; tanda negatif tetap tercantum pada angka.@endif</p>
        </div>
    @endif
    @if (!empty($chart['aggregation_note']))<p class="mt-5 border-t border-slate-100 pt-4 text-xs leading-5 text-slate-500">{{ $chart['aggregation_note'] }}</p>@endif
    <details class="mt-4 border-t border-slate-100 pt-3">
        <summary class="cursor-pointer text-xs font-semibold text-brand-800">Lihat tabel angka</summary>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <caption class="sr-only">{{ $chart['title'] }}{{ $unit ? ' dalam '.$unit : '' }}</caption>
                <thead class="border-b border-slate-200 text-xs text-slate-500"><tr><th scope="col" class="py-2 pr-4">Rincian</th><th scope="col" class="py-2 text-right">Angka{{ $unit ? ' ('.$unit.')' : '' }}</th></tr></thead>
                <tbody class="divide-y divide-slate-100">@foreach ($values as $index => $value)<tr><th scope="row" class="py-2 pr-4 font-normal">{{ $chart['labels'][$index] }}</th><td class="py-2 text-right font-semibold tabular-nums">{{ $number($value) }}</td></tr>@endforeach</tbody>
            </table>
        </div>
    </details>
</article>
