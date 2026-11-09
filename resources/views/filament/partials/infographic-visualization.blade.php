<div x-show="mode !== 'bar' && mode !== 'doughnut'" class="mt-5" x-cloak>
    <div class="overflow-x-auto rounded-xl border border-slate-100 bg-slate-50">
        <svg viewBox="0 0 680 350" style="width:100%;min-width:{{ max(480, count($values) * 38) }}px;display:block" role="img" aria-label="{{ $chart['title'] }}. Angka lengkap tersedia pada tabel di bawah.">
            <title>{{ $chart['title'] }}</title>
            @foreach ($layout['ticks'] as $tick)
                <line x1="80" x2="640" y1="{{ $tick['y'] }}" y2="{{ $tick['y'] }}" stroke="#e2e8f0" stroke-dasharray="4 4" />
                <text x="70" y="{{ $tick['y'] + 4 }}" text-anchor="end" fill="#64748b" font-size="11">{{ $number($tick['value']) }}</text>
            @endforeach
            <line x1="80" x2="640" y1="{{ $layout['zero'] }}" y2="{{ $layout['zero'] }}" stroke="#94a3b8" />
            <g x-show="mode === 'area'">
                <polygon points="{{ $layout['area'] }}" fill="#0d9488" fill-opacity="0.16" />
            </g>
            <g x-show="mode === 'line' || mode === 'area'">
                <polyline points="{{ $layout['polyline'] }}" fill="none" stroke="#0f766e" stroke-width="3" stroke-linejoin="round" />
            </g>
            @foreach ($layout['points'] as $index => $point)
                @php $color = $colors[$index % count($colors)]; @endphp
                <g x-show="mode === 'column'">
                    <rect x="{{ $point['x'] - $layout['step'] * 0.3 }}" y="{{ min($point['y'], $layout['zero']) }}" width="{{ $layout['step'] * 0.6 }}" height="{{ abs($point['y'] - $layout['zero']) }}" rx="3" fill="{{ $color }}">
                        <title>{{ $point['label'] }}: {{ $number($point['value']) }} {{ $unit }}</title>
                    </rect>
                </g>
                <g x-show="mode === 'dot'">
                    <line x1="{{ $point['x'] }}" x2="{{ $point['x'] }}" y1="{{ $layout['zero'] }}" y2="{{ $point['y'] }}" stroke="{{ $color }}" stroke-opacity="0.35" stroke-width="3" />
                </g>
                <g x-show="mode !== 'column'">
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="{{ $color }}" stroke="white" stroke-width="2">
                        <title>{{ $point['label'] }}: {{ $number($point['value']) }} {{ $unit }}</title>
                    </circle>
                </g>
                @if (count($values) <= 10)
                    <text x="{{ $point['x'] }}" y="{{ $point['y'] - 10 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#0f172a">{{ $number($point['value']) }}</text>
                @endif
                <text transform="translate({{ $point['x'] }},270) rotate(-35)" text-anchor="end" font-size="11" fill="#475569">
                    <title>{{ $point['label'] }}</title>{{ \Illuminate\Support\Str::limit($point['label'], 19) }}
                </text>
            @endforeach
        </svg>
    </div>
    <p class="mt-3 text-xs leading-5 text-slate-500" x-text="mode === 'line' || mode === 'area' ? 'Perubahan antarperiode sesuai urutan data. Arahkan penunjuk ke titik untuk melihat angkanya.' : 'Perbandingan setiap rincian pada skala yang sama. Arahkan penunjuk ke grafik untuk melihat angka dan label lengkap.'"></p>
</div>
@if ($layout['segments'])
    <div x-show="mode === 'doughnut'" class="mt-5" x-cloak>
        <svg viewBox="0 0 320 240" style="width:100%;max-height:260px" role="img" aria-label="Komposisi {{ $chart['title'] }}">
            <title>Komposisi {{ $chart['title'] }}</title>
            <circle cx="160" cy="120" r="82" fill="none" stroke="#f1f5f9" stroke-width="35" />
            @foreach ($layout['segments'] as $index => $segment)
                <circle cx="160" cy="120" r="82" fill="none" stroke="{{ $colors[$index % count($colors)] }}" stroke-width="35" pathLength="100" stroke-dasharray="{{ $segment['share'] }} {{ 100 - $segment['share'] }}" stroke-dashoffset="{{ -$segment['offset'] }}" transform="rotate(-90 160 120)">
                    <title>{{ $segment['label'] }}: {{ $number($segment['value']) }} {{ $unit }} ({{ $number(round($segment['share'], 1)) }}%)</title>
                </circle>
            @endforeach
            <text x="160" y="116" text-anchor="middle" fill="#0f172a" font-size="24" font-weight="700">100%</text>
            <text x="160" y="140" text-anchor="middle" fill="#64748b" font-size="12">rincian ditampilkan</text>
        </svg>
        <ul class="mt-3 space-y-3 text-sm">
            @foreach ($layout['segments'] as $index => $segment)
                <li class="flex items-start justify-between gap-3">
                    <span class="flex items-start gap-2 text-slate-600"><span style="background:{{ $colors[$index % count($colors)] }};width:10px;height:10px;flex-shrink:0;border-radius:3px;margin-top:5px"></span>{{ $segment['label'] }}</span>
                    <span class="text-right font-semibold text-slate-900">{{ $number($segment['value']) }} {{ $unit }} <span class="text-xs font-normal text-slate-500">({{ $number(round($segment['share'], 1)) }}%)</span></span>
                </li>
            @endforeach
        </ul>
        <p class="mt-4 text-xs leading-5 text-slate-500">Persentase dihitung dari jumlah rincian yang ditampilkan, bukan terhadap populasi di luar data ini.</p>
    </div>
@endif
