<x-filament::page>
    @php
        $number = fn ($value) => is_numeric($value) ? number_format((int) $value, 0, ',', '.') : $value;
        $colors = [
            'primary' => ['bg' => '#f0f7ff', 'text' => '#1e40af', 'bar' => '#3b82f6', 'border' => '#dbeafe', 'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)'],
            'success' => ['bg' => '#f0fdf4', 'text' => '#166534', 'bar' => '#22c55e', 'border' => '#dcfce7', 'gradient' => 'linear-gradient(135deg, #22c55e 0%, #16a34a 100%)'],
            'warning' => ['bg' => '#fffbeb', 'text' => '#92400e', 'bar' => '#f59e0b', 'border' => '#fef3c7', 'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'],
            'danger' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'bar' => '#ef4444', 'border' => '#fee2e2', 'gradient' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'],
            'info' => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'bar' => '#3b82f6', 'border' => '#bfdbfe', 'gradient' => 'linear-gradient(135deg, #60a5fa 0%, #2563eb 100%)'],
            'gray' => ['bg' => '#f8fafc', 'text' => '#475569', 'bar' => '#64748b', 'border' => '#e2e8f0', 'gradient' => 'linear-gradient(135deg, #64748b 0%, #475569 100%)'],
        ];
        $chartPalette = ['#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#6366f1', '#84cc16', '#f43f5e', '#14b8a6'];
        $markers = $dashboard['map']['markers'] ?? [];
    @endphp

    <style>
        :root {
            --occ-radius: 12px;
            --occ-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --occ-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }
        .occ-stack { display: grid; gap: 1.5rem; font-family: 'Inter', 'DM Sans', sans-serif; }
        .occ-panel, .occ-card { background: rgba(255,255,255,0.96); border: 1px solid rgba(148,163,184,0.18); border-radius: var(--occ-radius); box-shadow: var(--occ-shadow); backdrop-filter: blur(14px); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
        .occ-panel:hover, .occ-card:hover { transform: translateY(-2px); box-shadow: var(--occ-shadow-lg); border-color: rgba(148,163,184,0.28); }
        .occ-panel { padding: 1.5rem; }
        .occ-header { display: grid; gap: 1.25rem; padding: 1.5rem; background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(248,250,252,0.95)); border: 1px solid rgba(148,163,184,0.2); border-radius: var(--occ-radius); box-shadow: var(--occ-shadow); position: relative; overflow: hidden; }
        .occ-header::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: #2563eb; }
        .occ-header-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); }
        .occ-eyebrow { color: #64748b; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 2px; }
        .occ-title { color: #0f172a; font-size: 1.75rem; line-height: 1.2; font-weight: 800; }
        .occ-muted { color: #64748b; font-size: .875rem; }
        .occ-section-title { color: #0f172a; font-size: 1.1rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; }
        .occ-badge { display: inline-flex; width: fit-content; align-items: center; gap: .375rem; padding: .3rem .75rem; border-radius: 999px; background: var(--badge-bg); color: var(--badge-text); border: 1px solid var(--badge-border); font-size: .7rem; font-weight: 800; }
        .occ-kpi-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .occ-kpi { padding: 1.35rem 1.5rem; border: 1px solid rgba(148,163,184,0.14); border-radius: 18px; background: rgba(255,255,255,0.86); position: relative; overflow: hidden; min-height: 150px; display: grid; gap: 0.75rem; }
        .occ-kpi::after { content: ''; position: absolute; inset: 0; z-index: 0; background: radial-gradient(circle at top left, rgba(59,130,246,0.14), transparent 32%); pointer-events: none; }
        .occ-kpi > * { position: relative; z-index: 1; }
        .occ-kpi-value { margin-top: .15rem; color: #0f172a; font-size: 2.15rem; line-height: 1; font-weight: 900; letter-spacing: -0.03em; }
        .occ-task-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .occ-task { padding: 1.25rem 1.3rem 1.2rem 1.35rem; border-left: 5px solid var(--task-bar); background: rgba(255,255,255,0.95); border-radius: 18px; }
        .occ-task-count { margin-top: .65rem; color: #0f172a; font-size: 1.85rem; line-height: 1; font-weight: 900; letter-spacing: -0.04em; }
        .occ-progress-list { display: grid; gap: 1rem; }
        .occ-progress-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: .5rem; }
        .occ-progress-track { height: .6rem; overflow: hidden; border-radius: 999px; background: #e2e8f0; }
        .occ-progress-fill { height: 100%; width: var(--progress); border-radius: 999px; background: var(--bar); transition: width 1s ease-out; }
        .occ-split { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr); }
        .occ-chart-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .occ-chart { padding: 1.5rem; border-radius: 18px; border: 1px solid rgba(148,163,184,0.14); background: rgba(255,255,255,0.94); }
        .occ-chart-row { display: grid; grid-template-columns: minmax(100px, 35%) 1fr auto; gap: .75rem; align-items: center; margin-top: .8rem; font-size: .87rem; }
        .occ-chart-label { color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600; }
        .occ-chart-track { height: .6rem; border-radius: 999px; background: #f1f5f9; overflow: hidden; }
        .occ-chart-fill { display: block; height: 100%; width: var(--width); background: var(--bar); border-radius: 999px; transition: width 1s ease-out; }
        .occ-map { height: 520px; min-height: 420px; overflow: hidden; border: 1px solid #e2e8f0; border-radius: var(--occ-radius); box-shadow: inset 0 2px 4px 0 rgb(0 0 0 / 0.05); position: relative; }
        .occ-map-overlay-compact { position: absolute; left: 20px; top: 20px; z-index: 420; min-width: 180px; }
        .occ-map-overlay-compact .occ-map-card { padding: .7rem .85rem; border-radius: 12px; }
        .occ-marker-pin { width: 22px; height: 22px; border-radius: 22px; border: 2px solid #fff; box-shadow: 0 6px 12px rgba(15,23,42,.18); display:flex; align-items:center; justify-content:center; }
        .occ-map-tools { display: grid; gap: 1.25rem; grid-template-columns: minmax(0, 1.6fr) minmax(280px, 0.9fr); align-items: start; margin-bottom: 1rem; }
        .occ-map-main { display: grid; gap: 1rem; }
        .occ-map-side { display: grid; gap: 1rem; min-width: 0; align-self: stretch; }
        .occ-map-side > * { width: 100%; }
        .occ-map-search { display: grid; gap: .75rem; margin-bottom: 0; }
        @media (max-width: 940px) { .occ-map-tools { grid-template-columns: 1fr; } }
        .occ-search-input { width: 100%; padding: .95rem 1rem; border-radius: 16px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-size: .95rem; outline: none; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .occ-search-input:focus { border-color: #2563eb; box-shadow: 0 0 0 5px rgba(37,99,235,0.12); }
        .occ-map-search-hitlist { display: grid; gap: .55rem; max-height: 260px; overflow-y: auto; }
        .occ-map-search-hit { padding: .95rem 1rem; border-radius: 16px; border: 1px solid rgba(148,163,184,0.16); background: rgba(255,255,255,0.92); cursor: pointer; transition: background 0.2s ease; }
        .occ-map-search-hit:hover { background: rgba(248,250,252,0.98); }
        .occ-map-search-hit strong { display: block; color: #0f172a; font-size: .95rem; }
        .occ-map-search-hit small { color: #64748b; }
        .occ-map-summary { display: grid; gap: .7rem; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
        .occ-map-card { padding: .95rem 1rem; border-radius: 18px; background: #ffffff; border: 1px solid #e4e7ec; box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05); min-height: 96px; display: grid; grid-template-columns: auto 1fr; gap: .65rem; align-items: center; }
        .occ-map-card-icon { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; background: rgba(59,130,246,0.12); color: #2563eb; font-size: 1.05rem; box-shadow: inset 0 1px 2px rgba(15,23,42,0.08); }
        .occ-map-card strong { display: block; color: #334155; font-size: .78rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; margin-bottom: .35rem; }
        .occ-map-card span, .occ-map-card .occ-map-summary-value { color: #0f172a; font-size: 1.45rem; font-weight: 800; line-height: 1.05; }
        .occ-map-legend { display: grid; gap: .75rem; padding: 1rem 1.1rem; border-radius: 20px; background: rgba(255,255,255,0.98); border: 1px solid #e4e7ec; }
        .occ-map-legend h4 { margin: 0 0 .5rem 0; font-size: .95rem; color: #0f172a; }
        .occ-map-legend-item { display: flex; align-items: center; gap: .65rem; font-size: .83rem; color: #334155; }
        .occ-map-legend-bullet { width: 12px; height: 12px; border-radius: 999px; display: inline-block; }
        .occ-marker-pin { width: 16px; height: 16px; border-radius: 14px; border: 2px solid #fff; box-shadow: 0 4px 12px rgba(15,23,42,.16); }
        .occ-map-overlay { position: static; width: 100%; }
        .occ-map-overlay .occ-map-legend { backdrop-filter: none; background: rgba(255,255,255,.98); }
        .occ-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 2.25rem; padding: .5rem .875rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; color: #0f172a; font-size: .8125rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .occ-btn:hover { background: #f8fafc; border-color: #94a3b8; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .occ-list { display: grid; gap: .75rem; margin-top: 1rem; }
        .occ-list-item { display: flex; justify-content: space-between; gap: .875rem; padding: .95rem 1rem; border-radius: 16px; border: 1px solid rgba(148,163,184,0.16); background: rgba(255,255,255,0.88); transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease; text-decoration: none; color: inherit; }
        .occ-list-item:hover { background: rgba(248,250,252,0.95); border-color: rgba(148,163,184,0.25); transform: translateX(3px); box-shadow: 0 8px 20px rgba(15,23,42,0.08); }
        .occ-table-wrap { overflow-x: auto; margin-top: 1rem; border-radius: 16px; border: 1px solid rgba(148,163,184,0.16); background: rgba(255,255,255,0.92); }
        .occ-table { width: 100%; border-collapse: collapse; font-size: .875rem; background: transparent; }
        .occ-table th { text-align: left; color: #475569; font-weight: 800; background: rgba(248,250,252,0.95); text-transform: uppercase; font-size: .72rem; letter-spacing: .08em; padding: 1rem .95rem; }
        .occ-table th, .occ-table td { padding: .85rem .95rem; border-bottom: 1px solid rgba(226,232,240,0.7); white-space: nowrap; }
        .occ-table tr:last-child td { border-bottom: none; }
        .occ-action-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .occ-action { display: block; padding: 1.35rem 1.4rem; border: 1px solid rgba(148,163,184,0.14); border-radius: 18px; background: rgba(255,255,255,0.92); color: #0f172a; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
        .occ-action:hover { transform: translateY(-3px); box-shadow: var(--occ-shadow-lg); border-color: rgba(148,163,184,0.3); }
        .occ-action strong { color: #0f172a; font-size: 1rem; font-weight: 800; }
        .occ-action span { display: block; margin-top: .45rem; color: #64748b; }
        .occ-theme-switcher { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem .95rem; border-radius: 999px; border: 1px solid rgba(148,163,184,0.16); background: rgba(255,255,255,0.95); color: #0f172a; font-size: .87rem; font-weight: 700; cursor: pointer; transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease; }
        .occ-theme-switcher:hover { background: rgba(248,250,252,0.98); }
        body.occ-dark { color: #e2e8f0; background: #020617; }
        body.occ-dark .occ-panel,
        body.occ-dark .occ-card,
        body.occ-dark .occ-action,
        body.occ-dark .occ-kpi,
        body.occ-dark .occ-map-card,
        body.occ-dark .occ-map-legend,
        body.occ-dark .occ-list-item,
        body.occ-dark .occ-table-wrap,
        body.occ-dark .occ-task,
        body.occ-dark .occ-header { background: rgba(15,23,42,0.9); border-color: rgba(71,85,105,0.22); }
        body.occ-dark .occ-header::before { background: #60a5fa; }
        body.occ-dark .occ-title,
        body.occ-dark .occ-section-title,
        body.occ-dark .occ-map-card strong,
        body.occ-dark .occ-map-legend h4,
        body.occ-dark .occ-list-item strong,
        body.occ-dark .occ-table th { color: #f8fafc; }
        body.occ-dark .occ-muted,
        body.occ-dark .occ-action span,
        body.occ-dark .occ-chart-label,
        body.occ-dark .occ-map-legend-item,
        body.occ-dark .occ-map-search-hit small { color: #cbd5e1; }
        body.occ-dark .occ-btn { background: #15233b; border-color: #334155; color: #e2e8f0; }
        body.occ-dark .occ-btn:hover { background: #1f2f47; border-color: #64748b; }
        body.occ-dark .occ-search-input { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        body.occ-dark .occ-map-search-hit { background: rgba(15,23,42,0.92); border-color: rgba(71,85,105,0.22); }
        body.occ-dark .occ-map-search-hit:hover { background: rgba(31,41,55,0.94); }
        @media (max-width: 900px) { .occ-split { grid-template-columns: 1fr; } .occ-list-item { display: grid; } }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

    <div class="occ-stack">
        <section class="occ-header">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 1rem; align-items: start;">
                <div>
                    <div class="occ-eyebrow">{{ $dashboard['header']['badge'] }}</div>
                    <h2 class="occ-title">{{ $dashboard['header']['title'] }}</h2>
                    <p class="occ-muted" style="margin-top: .35rem;">{{ $dashboard['header']['summary'] }}</p>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;">
                    <button type="button" id="occ-theme-switcher" class="occ-theme-switcher" aria-pressed="false">Dark Mode</button>
                    @php($statusColor = $colors['success'])
                    <span class="occ-badge" style="--badge-bg: {{ $statusColor['bg'] }}; --badge-text: {{ $statusColor['text'] }}; --badge-border: {{ $statusColor['border'] }};">{{ $dashboard['header']['login_status'] }}</span>
                </div>
            </div>
            <div class="occ-header-grid">
                <div><div class="occ-eyebrow">{{ in_array($dashboard['mode'], ['kecamatan', 'operator'], true) ? 'Kecamatan' : 'Wilayah' }}</div><strong>{{ $dashboard['header']['scope'] }}</strong></div>
                <div><div class="occ-eyebrow">Operator</div><strong>{{ $dashboard['header']['operator'] }}</strong></div>
                <div><div class="occ-eyebrow">Tanggal</div><strong>{{ $dashboard['header']['date'] }}</strong></div>
                <div><div class="occ-eyebrow">Jam Server</div><strong>{{ $dashboard['header']['time'] }}</strong></div>
                <div><div class="occ-eyebrow">Jumlah Desa</div><strong>{{ $number($dashboard['header']['desa_total']) }}</strong></div>
            </div>
        </section>

        <section class="occ-kpi-grid" aria-label="KPI Pendataan">
            @foreach ($dashboard['kpis'] as $kpi)
                @php($color = $colors[$kpi['color']] ?? $colors['gray'])
                <article class="occ-kpi" style="--card-bg: {{ $color['bg'] }}; --card-border: {{ $color['border'] }};">
                    <div class="occ-muted" style="font-weight: 700; letter-spacing: .03em; text-transform: uppercase; font-size: .72rem;">{{ $kpi['label'] }}</div>
                    <div class="occ-kpi-value">{{ $number($kpi['value']) }}</div>
                    <div style="margin-top: .45rem; color: {{ $color['text'] }}; font-size: .82rem; font-weight: 700; opacity: .88;">{{ $kpi['description'] }}</div>
                </article>
            @endforeach
        </section>

        @if ($dashboard['mode'] === 'kabupaten')
            <section class="occ-kpi-grid" aria-label="Ringkasan Utama">
                @foreach ($dashboard['topCards'] as $card)
                    @php($color = $colors[$card['color']] ?? $colors['gray'])
                    <article class="occ-kpi" style="--card-bg: {{ $color['bg'] }}; --card-border: {{ $color['border'] }};">
                        <div class="occ-muted" style="font-weight: 700; letter-spacing: .03em; text-transform: uppercase; font-size: .72rem;">{{ $card['label'] }}</div>
                        <div class="occ-kpi-value">{{ $number($card['value']) }}</div>
                        <div style="display:flex; align-items:center; gap:.5rem; margin-top:.35rem; color: {{ $color['text'] }}; font-size:.92rem; font-weight:700;">
                            <span>{{ $card['trend'] }}</span>
                        </div>
                        <p class="occ-muted" style="margin-top:.65rem;">{{ $card['description'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="occ-split">
                <div class="occ-panel">
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem;"><span class="occ-badge" style="--badge-bg:#f0f7ff; --badge-text:#1e40af; --badge-border:#dbeafe;">Status Layanan</span><strong style="font-size:1rem; color:#0f172a;">Ringkasan Operasional</strong></div>
                    <div class="occ-task-grid">
                        @foreach ($dashboard['summaryPanel'] as $key => $value)
                            <article class="occ-card" style="padding:1rem; border-radius:16px; background:rgba(248,250,252,.96);">
                                <div style="font-size:.8rem; color:#64748b; text-transform:uppercase; letter-spacing:.08em; margin-bottom:.45rem;">{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</div>
                                <div style="font-size:1.45rem; font-weight:800; color:#0f172a;">{{ $value }}</div>
                            </article>
                        @endforeach
                    </div>
                </div>
                <div class="occ-panel">
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem;"><span class="occ-badge" style="--badge-bg:#f8fafc; --badge-text:#475569; --badge-border:#e2e8f0;">Verifikasi</span><strong style="font-size:1rem; color:#0f172a;">Status Batch</strong></div>
                    <div class="occ-task-grid">
                        @foreach ($dashboard['verificationSummary'] as $summary)
                            @php($color = $colors[$summary['color']] ?? $colors['gray'])
                            <article class="occ-card" style="padding:1rem; border-radius:16px; background:rgba(255,255,255,.96); border:1px solid {{ $color['border'] }};">
                                <div style="font-size:.85rem; color:{{ $color['text'] }}; font-weight:800; margin-bottom:.7rem;">{{ $summary['label'] }}</div>
                                <div style="font-size:2rem; color:#0f172a; font-weight:900;">{{ $number($summary['count']) }}</div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            @if ($dashboard['infographics_ready'])
            <section class="occ-chart-grid" aria-label="Visualisasi Kominfo">
                @foreach ($dashboard['quickStats'] as $stat)
                    <article class="occ-card occ-chart">
                        <h3 class="occ-section-title" style="margin-bottom: 1rem;">{{ $stat['title'] }}</h3>
                        <div style="min-height: 220px; display:grid; gap:.8rem;">
                            @php($chart = $stat['chart'])
                            <canvas id="quickstat-chart-{{ $loop->index }}" data-chart='@json($chart)' style="width:100%; height:220px;"></canvas>
                        </div>
                    </article>
                @endforeach
                @foreach ($dashboard['chartPanels'] as $panel)
                    <article class="occ-card occ-chart">
                        <h3 class="occ-section-title" style="margin-bottom: 1rem;">{{ $panel['title'] }}</h3>
                        <div style="min-height: 260px;">
                            <canvas id="chartpanel-chart-{{ $loop->index }}" data-chart='@json($panel['chart'])' style="width:100%; height:260px;"></canvas>
                        </div>
                    </article>
                @endforeach
            </section>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const renderChart = (canvas) => {
                        if (!canvas) return;
                        try {
                            const chartData = JSON.parse(canvas.dataset.chart || '{}');
                            const labels = Object.keys(chartData.data || {});
                            const data = Object.values(chartData.data || {});
                            const palette = @json($chartPalette);
                            const primary = palette[0] || '#3b82f6';
                            const background = chartData.type === 'line'
                                ? 'rgba(59,130,246,0.18)'
                                : labels.map((_, index) => palette[index % palette.length]);

                            const dataset = {
                                label: chartData.label || 'Data',
                                data,
                                borderColor: primary,
                                backgroundColor: background,
                                borderWidth: 2,
                                fill: chartData.type === 'line',
                                tension: chartData.type === 'line' ? 0.35 : 0,
                                pointRadius: chartData.type === 'line' ? 4 : 0,
                                pointHoverRadius: chartData.type === 'line' ? 6 : 4,
                                borderRadius: chartData.type === 'bar' ? 12 : 0,
                                barPercentage: 0.72,
                                categoryPercentage: 0.78,
                            };

                            const config = {
                                type: chartData.type || 'bar',
                                data: {
                                    labels,
                                    datasets: [dataset],
                                },
                                options: {
                                    maintainAspectRatio: false,
                                    responsive: true,
                                    plugins: {
                                        legend: { display: chartData.type === 'doughnut' || chartData.type === 'pie', position: 'bottom', labels: { color: '#64748b', boxWidth: 12, padding: 14 } },
                                        tooltip: { mode: 'index', intersect: false, titleColor: '#0f172a', bodyColor: '#0f172a', backgroundColor: 'rgba(15,23,42,0.92)' },
                                    },
                                    interaction: { mode: 'nearest', axis: 'x', intersect: false },
                                    scales: chartData.type === 'line' || chartData.type === 'bar'
                                        ? {
                                            x: { grid: { display: false, color: 'rgba(148,163,184,0.12)' }, ticks: { color: '#6b7280' } },
                                            y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.18)' }, ticks: { color: '#6b7280' } },
                                        }
                                        : {},
                                },
                            };

                            // eslint-disable-next-line no-undef
                            new Chart(canvas.getContext('2d'), config);
                        } catch (e) {
                            console.warn('Unable to render dashboard chart', e);
                        }
                    };

                    document.querySelectorAll('canvas[data-chart]').forEach(renderChart);

                    const themeToggle = document.getElementById('occ-theme-switcher');
                    const storedTheme = window.localStorage.getItem('occ-theme');
                    const applyTheme = (theme) => {
                        document.body.classList.toggle('occ-dark', theme === 'dark');
                        themeToggle.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
                        themeToggle.setAttribute('aria-pressed', theme === 'dark');
                        window.localStorage.setItem('occ-theme', theme);
                    };
                    if (storedTheme) {
                        applyTheme(storedTheme);
                    }
                    themeToggle?.addEventListener('click', () => applyTheme(document.body.classList.contains('occ-dark') ? 'light' : 'dark'));
                });
            </script>

                <section class="occ-panel" style="position: relative;">
                    <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:1rem; align-items:start; margin-bottom:1.25rem;">
                        <div>
                            <span class="occ-badge" style="--badge-bg:#eff6ff; --badge-text:#1d4ed8; --badge-border:#bfdbfe;">Peta Digital</span>
                            <h3 class="occ-section-title" style="margin-top:.55rem;">Peta Digital Pintar</h3>
                            <p class="occ-muted">Visualisasi rumah verifikasi dan distribusi penduduk di wilayah kabupaten secara interaktif.</p>
                        </div>
                        <div class="occ-map-summary" style="display:grid; gap:.8rem; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); width:min(520px,100%);">
                            <div class="occ-map-card"><span class="occ-map-card-icon">📍</span><div><strong>Total Titik</strong><span class="occ-map-summary-value">{{ number_format(count($dashboard['map']['markers'] ?? [])) }}</span></div></div>
                            <div class="occ-map-card"><span class="occ-map-card-icon">🏠</span><div><strong>Rumah Terpeta</strong><span class="occ-map-summary-value">{{ number_format($dashboard['map']['total'] ?? 0) }}</span></div></div>
                            <div class="occ-map-card"><span class="occ-map-card-icon">⚠️</span><div><strong>Tanpa Koordinat</strong><span class="occ-map-summary-value">{{ number_format($dashboard['map']['unmapped'] ?? 0) }}</span></div></div>
                            <div class="occ-map-card"><span class="occ-map-card-icon">👥</span><div><strong>Rata-rata Penduduk</strong><span class="occ-map-summary-value">{{ $dashboard['map']['average_residents'] ?? 0 }}</span></div></div>
                        </div>
                    </div>
                    <div class="occ-map-tools">
                        <div class="occ-map-main">
                            <div class="occ-map-search">
                                <input id="dashboard-map-search" type="search" class="occ-search-input" placeholder="Cari desa, alamat, atau kode rumah..." autocomplete="off" />
                                <div id="dashboard-map-search-results" class="occ-map-search-hitlist"></div>
                            </div>
                            <div id="dashboard-smart-map" class="occ-map"></div>
                        </div>
                        <div class="occ-map-side">
                            <div class="occ-map-legend">
                                <h4>Legenda Status Rumah</h4>
                                <div class="occ-map-legend-item"><span class="occ-map-legend-bullet" style="background:#22c55e"></span>Terverifikasi</div>
                                <div class="occ-map-legend-item"><span class="occ-map-legend-bullet" style="background:#f59e0b"></span>Perlu Verifikasi</div>
                                <div class="occ-map-legend-item"><span class="occ-map-legend-bullet" style="background:#ef4444"></span>Belum Lengkap</div>
                            </div>
                            <div class="occ-map-summary" style="grid-template-columns:1fr 1fr; gap:.8rem; margin-top:.75rem;">
                                <div class="occ-map-card"><span class="occ-map-card-icon">🧭</span><div><strong>Rumah Dipetakan</strong><span class="occ-map-summary-value">{{ number_format($dashboard['map']['count'] ?? 0) }}</span></div></div>
                                <div class="occ-map-card"><span class="occ-map-card-icon">📊</span><div><strong>Variasi Status</strong><span class="occ-map-summary-value">{{ count(array_unique(array_map(fn($marker) => $marker['status_rumah'] ?? 'Lainnya', $markers))) }}</span></div></div>
                            </div>
                        </div>
                    </div>
                    @if (empty($markers))
                        <div style="margin-top: .85rem;">
                            <div class="occ-map-card" style="background:#fff8e1; border:1px solid #fef3c7;">Tidak ditemukan marker terverifikasi — menampilkan placeholder pada peta.</div>
                        </div>
                    @endif
                </section>

            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const markers = @json($markers);
                    const hasMarkers = Array.isArray(markers) && markers.length > 0;
                    const fallbackLatLng = [3.6, 125.45];

                    const mapEl = document.getElementById('dashboard-smart-map');
                    const resultsEl = document.getElementById('dashboard-map-search-results');
                    const searchEl = document.getElementById('dashboard-map-search');
                    const map = L.map(mapEl).setView(hasMarkers ? [markers[0].latitude, markers[0].longitude] : fallbackLatLng, 11);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19,
                    }).addTo(map);

                    const cluster = L.markerClusterGroup({ chunkedLoading: true });
                    const statusColors = {
                        'Terverifikasi': '#22c55e',
                        'Perlu Verifikasi': '#f59e0b',
                        'Belum Lengkap': '#ef4444',
                    };
                    const markerItems = (hasMarkers ? markers : []).map((marker) => {
                        const safeLabel = `${marker.kode_rumah || ''} ${marker.alamat || ''} ${marker.desa || ''}`.trim();
                        const fillColor = statusColors[marker.status_rumah] ?? '#3b82f6';
                        const icon = L.divIcon({
                            className: '',
                            html: `<div class="occ-marker-pin" style="background:${fillColor};"><span style="font-size:10px;color:#fff;">•</span></div>`,
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                            popupAnchor: [0, -12],
                        });
                        const leafletMarker = L.marker([marker.latitude, marker.longitude], { icon });
                        leafletMarker.bindPopup(`
                            <div style="min-width:220px;line-height:1.35;">
                                <strong style="font-size:1rem;">${marker.kode_rumah || 'Rumah'}</strong>
                                <div style="margin:.35rem 0 .55rem;color:#475569;font-size:.9rem;">${marker.alamat || 'Alamat tidak tersedia'}</div>
                                <div style="font-size:.85rem;color:#334155;">Desa: ${marker.desa || '-'}</div>
                                <div style="font-size:.85rem;color:#334155; margin-top:.25rem;">KK: ${marker.jumlah_kk ?? 0} · Penduduk: ${marker.jumlah_penduduk ?? 0}</div>
                            </div>
                        `);
                        leafletMarker.searchLabel = safeLabel.toLowerCase();
                        return { marker: leafletMarker, label: safeLabel, data: marker };
                    });

                    if (markerItems.length) {
                        markerItems.forEach(({ marker }) => cluster.addLayer(marker));
                        map.addLayer(cluster);

                        const featureGroup = L.featureGroup(markerItems.map((item) => item.marker));
                        map.fitBounds(featureGroup.getBounds().pad(0.14));
                    } else {
                        // Placeholder location: Kabupaten Kepulauan Sangihe approximate center
                        const placeholderLatLng = [3.6, 125.45];
                        const placeholderIcon = L.divIcon({
                            className: 'occ-marker-pin',
                            html: '<span style="font-size:10px;color:#fff;">?</span>',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                        });
                        const placeholder = L.marker(placeholderLatLng, { icon: placeholderIcon }).addTo(map);
                        placeholder.bindPopup('<strong>Placeholder</strong><div>Tidak ada data lokasi terverifikasi. &#13;Periksa filter atau tambahkan koordinat pada data rumah.</div>');
                        map.setView(placeholderLatLng, 11);
                    }

                    const renderResults = (query) => {
                        const lowerQuery = query.trim().toLowerCase();
                        resultsEl.innerHTML = '';
                        if (!lowerQuery) {
                            return;
                        }
                        markerItems
                            .filter((item) => item.label.includes(lowerQuery))
                            .slice(0, 10)
                            .forEach((item) => {
                                const hit = document.createElement('button');
                                hit.type = 'button';
                                hit.className = 'occ-map-search-hit';
                                hit.innerHTML = `<strong>${item.data.kode_rumah || 'Rumah'}</strong><small>${item.label}</small>`;
                                hit.addEventListener('click', () => {
                                    map.setView(item.marker.getLatLng(), 16, { animate: true });
                                    item.marker.openPopup();
                                });
                                resultsEl.appendChild(hit);
                            });
                    };

                    searchEl?.addEventListener('input', (event) => renderResults(event.target.value));
                    searchEl?.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            searchEl.value = '';
                            resultsEl.innerHTML = '';
                        }
                    });
                });
            </script>

            <section class="occ-split">
                <div class="occ-panel">
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem;"><span class="occ-badge" style="--badge-bg:#eff6ff; --badge-text:#1d4ed8; --badge-border:#bfdbfe;">Notifikasi</span><strong style="font-size:1rem; color:#0f172a;">Update Terbaru</strong></div>
                    <div class="occ-list">
                        @forelse ($dashboard['latestNotifications'] as $note)
                            <div class="occ-list-item" style="flex-direction:column; align-items:flex-start; gap:.4rem;">
                                <strong style="color:#0f172a;">{{ $note['title'] }}</strong>
                                <span class="occ-muted">{{ $note['message'] }}</span>
                                <span class="occ-muted" style="font-size:.78rem;">{{ $note['time_label'] }}</span>
                            </div>
                        @empty
                            <p class="occ-muted">Belum ada notifikasi.</p>
                        @endforelse
                    </div>
                </div>
                <div class="occ-panel">
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem;"><span class="occ-badge" style="--badge-bg:#ecfdf5; --badge-text:#166534; --badge-border:#d1fae5;">Alert</span><strong style="font-size:1rem; color:#0f172a;">Sistem & Operasional</strong></div>
                    <div class="occ-list">
                        @foreach ($dashboard['systemAlerts'] as $alert)
                            @php($severityColor = match ($alert['severity']) {
                                'success' => $colors['success'],
                                'warning' => $colors['warning'],
                                'danger' => $colors['danger'],
                                default => $colors['gray'],
                            })
                            <div class="occ-list-item" style="border-left:4px solid {{ $severityColor['bar'] }};">
                                <div>
                                    <strong style="color:#0f172a;">{{ $alert['title'] }}</strong>
                                    <p class="occ-muted" style="margin: .35rem 0 0;">{{ $alert['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="occ-panel">
                    <div style="margin-bottom:1rem; display:flex; align-items:center; gap:.75rem;"><span class="occ-badge" style="--badge-bg:#f8fafc; --badge-text:#475569; --badge-border:#e2e8f0;">Perhatian</span><strong style="font-size:1rem; color:#0f172a;">Kecamatan Prioritas</strong></div>
                    <div class="occ-list">
                        @foreach ($dashboard['attentionList'] as $item)
                            @php($color = $colors[$item['color']] ?? $colors['gray'])
                            <div class="occ-list-item">
                                <div>
                                    <strong style="color:#0f172a;">{{ $item['label'] }}</strong>
                                    <p class="occ-muted" style="margin:.35rem 0 0;">{{ $item['description'] }}</p>
                                </div>
                                <span class="occ-badge" style="--badge-bg: {{ $color['bg'] }}; --badge-text: {{ $color['text'] }}; --badge-border: {{ $color['border'] }};">{{ $item['count'] }}{{ $item['suffix'] ?? '%' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
        @else
            @php
                $taskCenter = $dashboard['taskCenter'] ?? [
                    'heading' => 'Task Center',
                    'description' => 'Belum ada ringkasan tugas untuk mode ini.',
                    'items' => [],
                ];
                $progressItems = $dashboard['progressItems'] ?? [];
                $villageProgress = $dashboard['villageProgress'] ?? [];
                $charts = $dashboard['charts'] ?? [];
            @endphp
            <section class="occ-panel">
                <div style="margin-bottom: 1.25rem;">
                    <h3 class="occ-section-title">
                        <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 000 4h12a2 2 0 000-4M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        {{ $taskCenter['heading'] }}
                    </h3>
                    <p class="occ-muted">{{ $taskCenter['description'] }}</p>
                </div>
                <div class="occ-task-grid">
                    @foreach ($taskCenter['items'] as $task)
                        @php($color = $colors[$task['color']] ?? $colors['gray'])
                        <article class="occ-card occ-task" style="--task-bar: {{ $color['bar'] }};">
                            <div style="display: flex; justify-content: space-between; gap: .75rem; align-items: start;">
                                <strong style="font-size: 0.95rem; color: #334155;">{{ $task['label'] }}</strong>
                                <span class="occ-badge" style="--badge-bg: {{ $color['bg'] }}; --badge-text: {{ $color['text'] }}; --badge-border: {{ $color['border'] }}; font-size: .72rem; padding: .28rem .7rem; letter-spacing: .01em;">{{ $task['status'] }}</span>
                            </div>
                            @if ($task['available'])
                                <div class="occ-task-count" style="letter-spacing: -.02em;">{{ $number($task['count']) }}{{ $task['suffix'] ?? '' }}</div>
                            @else
                                <div class="occ-muted" style="margin-top: .65rem; font-weight: 800;">Belum tersedia pada struktur data.</div>
                            @endif
                            <p class="occ-muted" style="margin-top: .55rem;">{{ $task['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="occ-split">
                <div class="occ-panel">
                    <div style="margin-bottom: 1.25rem;">
                        <h3 class="occ-section-title">
                            <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            Progress Pendataan
                        </h3>
                        <p class="occ-muted">Dihitung dari status workflow existing.</p>
                    </div>
                    <div class="occ-progress-list">
                        @foreach ($progressItems as $item)
                            @php($color = $colors[$item['color']] ?? $colors['gray'])
                            <div>
                                <div class="occ-progress-head">
                                    <strong style="color: #334155;">{{ $item['label'] }}</strong>
                                    <span class="occ-muted" style="font-weight: 600;">{{ $number($item['done']) }} / {{ $number($item['total']) }} - {{ $item['percent'] }}%</span>
                                </div>
                                <div class="occ-progress-track">
                                    <div class="occ-progress-fill" style="--progress: {{ $item['percent'] }}%; --bar: {{ $color['bar'] }};"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if ($dashboard['mode'] === 'kecamatan')
                    <div class="occ-panel">
                        <h3 class="occ-section-title">Progress per Desa</h3>
                        <div class="occ-list">
                            @forelse ($villageProgress as $village)
                                @php($color = $colors[$village['color']] ?? $colors['gray'])
                                <div>
                                    <div class="occ-progress-head">
                                        <strong style="color: #334155;">{{ $village['nama'] }}</strong>
                                        <span class="occ-badge" style="--badge-bg: {{ $color['bg'] }}; --badge-text: {{ $color['text'] }}; --badge-border: {{ $color['border'] }};">{{ $village['status'] }}</span>
                                    </div>
                                    <div class="occ-progress-track"><div class="occ-progress-fill" style="--progress: {{ $village['progress'] }}%; --bar: {{ $color['bar'] }};"></div></div>
                                    <div class="occ-muted" style="margin-top: .35rem; font-size: 0.75rem;">{{ $number($village['rumah']) }} rumah, {{ $number($village['keluarga']) }} KK, {{ $number($village['penduduk']) }} penduduk</div>
                                </div>
                            @empty
                                <p class="occ-muted">Belum ada desa untuk ditampilkan.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </section>

            @if (! empty($charts))
                <section class="occ-chart-grid" aria-label="Statistik">
                    @php($chartIndex = 0)
                    @foreach ($charts as $key => $chart)
                        <article class="occ-card occ-chart">
                            <h3 class="occ-section-title" style="margin-bottom: 1rem;">{{ $chart['label'] }}</h3>
                            <div style="min-height:160px;">
                                <canvas id="dashboard-chart-{{ $chartIndex }}" style="max-height:320px; width:100%;"></canvas>
                            </div>
                            <noscript>
                                @php($max = max(array_values($chart['data']) ?: [0]))
                                @php($index = 0)
                                @forelse ($chart['data'] as $label => $value)
                                    @php($width = $max > 0 ? max(4, round(((int) $value / $max) * 100)) : 0)
                                    @php($bar = $chartPalette[$index % count($chartPalette)])
                                    @php($index++)
                                    <div class="occ-chart-row">
                                        <span class="occ-chart-label" title="{{ $label }}">{{ $label }}</span>
                                        <span class="occ-chart-track"><span class="occ-chart-fill" style="--width: {{ $width }}%; --bar: {{ $bar }};"></span></span>
                                        <strong style="color: #0f172a;">{{ $number($value) }}</strong>
                                    </div>
                                @empty
                                    <p class="occ-muted" style="margin-top: .75rem;">Belum ada data statistik.</p>
                                @endforelse
                            </noscript>
                        </article>
                        @php($chartIndex++)
                    @endforeach
                </section>
            @endif

            <section class="occ-panel">
                <h3 class="occ-section-title">
                    <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $dashboard['mode'] === 'kecamatan' ? 'Aktivitas Terbaru' : 'Aktivitas Kabupaten' }}
                </h3>
                <div class="occ-list">
                    @forelse ($dashboard['activityTimeline'] as $activity)
                        <a href="{{ $activity['link'] ?? '#' }}" class="occ-list-item">
                            <span>
                                <strong style="color: #0f172a;">{{ $activity['title'] }}</strong>
                                <span class="occ-muted" style="display: block;">{{ $activity['badge'] }} - {{ $activity['meta'] }}</span>
                            </span>
                            <span class="occ-muted" style="font-weight: 600; font-size: 0.75rem;">{{ $activity['time_label'] }}</span>
                        </a>
                    @empty
                        <p class="occ-muted">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </section>

            @if ($dashboard['mode'] === 'kabupaten')
                <section class="occ-panel">
                    <h3 class="occ-section-title">
                        <svg style="width: 1.25rem; height: 1.25rem; color: #3b82f6;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        {{ $dashboard['monitoring']['heading'] }}
                    </h3>
                    <div class="occ-table-wrap">
                        <table class="occ-table">
                            <thead>
                                <tr>
                                    <th>Nama Kecamatan</th>
                                    <th>Rumah</th>
                                    <th>KK</th>
                                    <th>Penduduk</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dashboard['monitoring']['rows'] as $row)
                                    @php($color = $colors[$row['color']] ?? $colors['gray'])
                                    <tr>
                                        <td><strong style="color: #0f172a;">{{ $row['nama'] }}</strong></td>
                                        <td>{{ $number($row['rumah']) }}</td>
                                        <td>{{ $number($row['keluarga']) }}</td>
                                        <td>{{ $number($row['penduduk']) }}</td>
                                        <td><span style="font-weight: 800; color: #334155;">{{ $row['progress'] }}%</span></td>
                                        <td><span class="occ-badge" style="--badge-bg: {{ $color['bg'] }}; --badge-text: {{ $color['text'] }}; --badge-border: {{ $color['border'] }};">{{ $row['status'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="occ-muted" style="text-align: center;">Belum ada data kecamatan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endif

        <section class="occ-action-grid" aria-label="Quick Action">
            @foreach ($dashboard['quickActions'] as $action)
                @php($color = $colors[$action['color']] ?? $colors['gray'])
                <a class="occ-action" href="{{ $action['url'] }}" style="--card-bg: {{ $color['bg'] }}; --card-border: {{ $color['border'] }}; --card-text: {{ $color['text'] }};">
                    <strong>{{ $action['label'] }}</strong>
                    <span class="occ-muted" style="display: block; margin-top: .35rem;">{{ $action['description'] }}</span>
                </a>
            @endforeach
        </section>
    </div>
</x-filament::page>
