<x-filament::page>
@php
    $number = fn ($value) => is_numeric($value) ? number_format((int) $value, 0, ',', '.') : $value;
    $colors = [
        'primary' => ['bg' => '#e3f0ff', 'text' => '#075ba7', 'bar' => '#0868b9', 'border' => '#b9d7f2', 'gradient' => 'linear-gradient(135deg, #0868b9 0%, #0b87d5 100%)', 'light' => '#3f9ddd'],
        'success' => ['bg' => '#e8f7ef', 'text' => '#16704a', 'bar' => '#28a66a', 'border' => '#bce6cf', 'gradient' => 'linear-gradient(135deg, #28a66a 0%, #168151 100%)', 'light' => '#54bd85'],
        'warning' => ['bg' => '#fff5dd', 'text' => '#9a6000', 'bar' => '#d99412', 'border' => '#f2d697', 'gradient' => 'linear-gradient(135deg, #d99412 0%, #b97709 100%)', 'light' => '#e7b348'],
        'danger'  => ['bg' => '#fff0f1', 'text' => '#b01e27', 'bar' => '#d81f26', 'border' => '#f3bec2', 'gradient' => 'linear-gradient(135deg, #d81f26 0%, #ee3b43 100%)', 'light' => '#e85b61'],
        'info'    => ['bg' => '#e6f5ff', 'text' => '#08709b', 'bar' => '#1296c4', 'border' => '#b8e2f2', 'gradient' => 'linear-gradient(135deg, #1296c4 0%, #08709b 100%)', 'light' => '#49acd0'],
        'teal'    => ['bg' => '#e5f8f5', 'text' => '#087268', 'bar' => '#149a8a', 'border' => '#b7e6df', 'gradient' => 'linear-gradient(135deg, #149a8a 0%, #087268 100%)', 'light' => '#4fb6a9'],
        'gold'    => ['bg' => '#fff5dd', 'text' => '#9a6000', 'bar' => '#d99412', 'border' => '#f2d697', 'gradient' => 'linear-gradient(135deg, #d99412 0%, #b97709 100%)', 'light' => '#e7b348'],
        'gray'    => ['bg' => '#f1f5f9', 'text' => '#486581', 'bar' => '#7b96b1', 'border' => '#d7e3f1', 'gradient' => 'linear-gradient(135deg, #7b96b1 0%, #56718f 100%)', 'light' => '#9db4ca'],
    ];
    $chartPalette = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#14b8a6', '#ec4899', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'];
    $markers = $dashboard['map']['markers'] ?? [];
    $taskCenter = $dashboard['taskCenter'] ?? ['heading' => 'Task Center', 'description' => 'Belum ada ringkasan tugas.', 'items' => []];
    $progressItems = $dashboard['progressItems'] ?? [];
    $villageProgress = $dashboard['villageProgress'] ?? [];
    $charts = $dashboard['charts'] ?? [];
@endphp
<style>
:root { --dash-bg: #f4f8fd; --dash-surface: #ffffff; --dash-navy: #063b78; --dash-accent: #0868b9; --dash-accent-mid: #0b87d5; --dash-teal: #149a8a; --dash-gold: #d81f26; --dash-text: #102a43; --dash-text-muted: #627d98; --dash-border: #d7e3f1; --dash-radius: 16px; --dash-radius-lg: 24px; --dash-shadow: 0 1px 3px rgba(23,64,112,.06), 0 1px 2px rgba(23,64,112,.04); --dash-shadow-md: 0 6px 14px rgba(23,64,112,.10); --dash-shadow-lg: 0 14px 28px rgba(23,64,112,.14); --dash-shadow-xl: 0 24px 45px rgba(23,64,112,.18); --dash-font: "DM Sans", ui-sans-serif, system-ui, -apple-system, sans-serif; }
* { box-sizing: border-box; }
.dash { font-family: var(--dash-font); color: var(--dash-text); background: var(--dash-bg); padding: 1.5rem; display: grid; gap: 1.5rem; max-width: 1600px; margin: 0 auto; }
.dash-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #334155 100%); border-radius: var(--dash-radius-lg); padding: 2rem 2.25rem; color: white; position: relative; overflow: hidden; box-shadow: var(--dash-shadow-xl); }
.dash-header::before { content: ""; position: absolute; top: -80px; right: -80px; width: 320px; height: 320px; border-radius: 50%; background: rgba(99,102,241,0.12); pointer-events: none; }
.dash-header::after { content: ""; position: absolute; bottom: -60px; right: 80px; width: 180px; height: 180px; border-radius: 50%; background: rgba(13,148,136,0.1); pointer-events: none; }
.dash-header-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-top: 1.25rem; }
.dash-header-meta { position: relative; z-index: 1; }
.dash-header-eyebrow { color: rgba(255,255,255,0.5); font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px; }
.dash-header-value { color: white; font-size: 1.05rem; font-weight: 800; line-height: 1.3; }
.dash-title { font-size: 1.6rem; font-weight: 900; letter-spacing: -0.03em; color: white; position: relative; z-index: 1; }
.dash-subtitle { color: rgba(255,255,255,0.6); font-size: 0.875rem; margin-top: 0.35rem; position: relative; z-index: 1; }
.dash-badge { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.3rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.03em; }
.dash-header-actions { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center; margin-top: 1.25rem; position: relative; z-index: 1; }
.dash-kpi-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
.dash-kpi { background: var(--dash-surface); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); padding: 1.5rem; position: relative; overflow: hidden; box-shadow: var(--dash-shadow); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; }
.dash-kpi:hover { transform: translateY(-3px); box-shadow: var(--dash-shadow-lg); border-color: rgba(99,102,241,0.2); }
.dash-kpi::after { content: ""; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--kpi-color); border-radius: var(--dash-radius) var(--dash-radius) 0 0; }
.dash-kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; background: var(--kpi-icon-bg); color: var(--kpi-icon-color); font-size: 1.2rem; box-shadow: var(--dash-shadow-md); }
.dash-kpi-label { color: var(--dash-text-muted); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }
.dash-kpi-value { color: var(--dash-text); font-size: 2rem; font-weight: 900; letter-spacing: -0.04em; line-height: 1; }
.dash-kpi-desc { color: var(--dash-text-muted); font-size: 0.8rem; font-weight: 600; margin-top: 0.5rem; }
.dash-kpi-trend { display: inline-flex; align-items: center; gap: 0.25rem; color: var(--kpi-icon-color); font-size: 0.8rem; font-weight: 700; margin-top: 0.5rem; }
.dash-card { background: var(--dash-surface); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); padding: 1.5rem; box-shadow: var(--dash-shadow); }
.dash-card:hover { box-shadow: var(--dash-shadow-md); }
.dash-card-title { font-size: 1rem; font-weight: 800; color: var(--dash-text); margin: 0 0 0.25rem 0; display: flex; align-items: center; gap: 0.5rem; }
.dash-card-subtitle { color: var(--dash-text-muted); font-size: 0.82rem; margin: 0 0 1.25rem 0; }
.dash-section-title { font-size: 1.05rem; font-weight: 800; color: var(--dash-text); margin: 0 0 1.25rem 0; display: flex; align-items: center; gap: 0.5rem; }
.dash-section-title::after { content: ""; flex: 1; height: 1px; background: var(--dash-border); margin-left: 0.5rem; }
.dash-split { display: grid; gap: 1.5rem; grid-template-columns: 1.3fr 0.7fr; }
.dash-3col { display: grid; gap: 1rem; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.dash-2col { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
@media(max-width: 900px) { .dash-split, .dash-3col, .dash-2col { grid-template-columns: 1fr; } }
.dash-stat-grid { display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
.dash-stat-card { background: #f8fafc; border: 1px solid var(--dash-border); border-radius: 12px; padding: 1rem; text-align: center; transition: background 0.2s ease; }
.dash-stat-card:hover { background: #f1f5f9; }
.dash-stat-value { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.03em; color: var(--dash-text); line-height: 1; }
.dash-stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--dash-text-muted); margin-top: 0.35rem; }
.dash-status { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.65rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; }
.dash-status-success { background: #ecfdf5; color: #065f46; }
.dash-status-warning { background: #fffbeb; color: #92400e; }
.dash-status-danger { background: #fef2f2; color: #991b1b; }
.dash-status-info { background: #eff6ff; color: #1e40af; }
.dash-status-neutral { background: #f8fafc; color: #475569; }
.dash-progress { margin-top: 1rem; }
.dash-progress-item { margin-bottom: 1rem; }
.dash-progress-item:last-child { margin-bottom: 0; }
.dash-progress-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
.dash-progress-label { font-size: 0.85rem; font-weight: 700; color: var(--dash-text); }
.dash-progress-val { font-size: 0.82rem; font-weight: 800; color: var(--dash-text-muted); }
.dash-progress-track { height: 8px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.dash-progress-fill { height: 100%; border-radius: 999px; transition: width 1s ease-out; }
.dash-list { display: grid; gap: 0.75rem; }
.dash-list-item { display: flex; align-items: center; justify-content: space-between; gap: 0.875rem; padding: 1rem 1.1rem; background: #f8fafc; border: 1px solid var(--dash-border); border-radius: 12px; border-left: 4px solid var(--list-bar, #64748b); transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.15s ease; text-decoration: none; color: inherit; }
.dash-list-item:hover { background: #f1f5f9; transform: translateX(3px); box-shadow: var(--dash-shadow-md); }
.dash-list-title { font-size: 0.9rem; font-weight: 700; color: var(--dash-text); display: block; }
.dash-list-desc { font-size: 0.8rem; color: var(--dash-text-muted); margin-top: 0.2rem; }
.dash-action-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }
.dash-action { display: flex; align-items: center; gap: 0.875rem; padding: 1.25rem; background: var(--dash-surface); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); text-decoration: none; color: inherit; transition: all 0.2s ease; box-shadow: var(--dash-shadow); }
.dash-action:hover { transform: translateY(-3px); box-shadow: var(--dash-shadow-lg); border-color: rgba(99,102,241,0.3); }
.dash-action-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.dash-action-title { font-size: 0.9rem; font-weight: 800; color: var(--dash-text); }
.dash-action-desc { font-size: 0.8rem; color: var(--dash-text-muted); margin-top: 0.2rem; }
.dash-chart-wrap { position: relative; height: 260px; }
.dash-map-container { border: 1px solid var(--dash-border); border-radius: var(--dash-radius); overflow: hidden; box-shadow: var(--dash-shadow); background: var(--dash-surface); }
.dash-map-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--dash-border); background: linear-gradient(135deg, #0f172a, #1e293b); color: white; }
.dash-map-search { width: 100%; padding: 0.875rem 1rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.1); color: white; font-size: 0.9rem; outline: none; backdrop-filter: blur(8px); }
.dash-map-search::placeholder { color: rgba(255,255,255,0.5); }
.dash-map-search:focus { border-color: rgba(255,255,255,0.4); background: rgba(255,255,255,0.15); }
.dash-map-body { height: 480px; }
.dash-map-sidebar { padding: 1.25rem; display: grid; gap: 0.75rem; grid-template-columns: repeat(2, 1fr); }
.dash-map-stat { background: #f8fafc; border: 1px solid var(--dash-border); border-radius: 12px; padding: 0.875rem; text-align: center; }
.dash-map-stat-val { font-size: 1.25rem; font-weight: 900; color: var(--dash-text); letter-spacing: -0.03em; }
.dash-map-stat-lbl { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--dash-text-muted); margin-top: 0.2rem; }
.dash-legend { display: flex; flex-wrap: wrap; gap: 0.75rem; padding: 1rem 1.25rem; border-top: 1px solid var(--dash-border); }
.dash-table-wrap { overflow-x: auto; margin-top: 1rem; border-radius: var(--dash-radius); border: 1px solid var(--dash-border); }
.dash-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; background: transparent; }
.dash-table th { text-align: left; padding: 0.875rem 1rem; background: #f8fafc; color: var(--dash-text-muted); font-weight: 800; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.06em; border-bottom: 1px solid var(--dash-border); white-space: nowrap; }
.dash-table td { padding: 0.875rem 1rem; border-bottom: 1px solid #f8fafc; color: var(--dash-text); }
.dash-table tr:last-child td { border-bottom: none; }
.dash-table tr:hover td { background: #f8fafc; }
body.dark .dash { --dash-bg: #020617; --dash-surface: #0f172a; --dash-text: #f1f5f9; --dash-text-muted: #94a3b8; --dash-border: rgba(51,65,85,0.4); }
body.dark .dash-kpi, body.dark .dash-card { background: #0f172a; border-color: rgba(51,65,85,0.4); }
body.dark .dash-stat-card { background: rgba(51,65,85,0.3); }
body.dark .dash-list-item { background: rgba(51,65,85,0.3); border-color: rgba(51,65,85,0.4); }
body.dark .dash-table th { background: rgba(51,65,85,0.3); }
body.dark .dash-table tr:hover td { background: rgba(51,65,85,0.3); }

/* Keep the dashboard visually calm, readable, and consistent with the admin shell. */
.dash-header { background: radial-gradient(circle at 86% 10%, rgba(45,212,191,.22), transparent 27%), linear-gradient(125deg, #0a1b2c 0%, #102c3d 54%, #0e4a4b 100%); border: 1px solid rgba(94,234,212,.18); }
.dash-kpi, .dash-card, .dash-map-container { background: linear-gradient(145deg, rgba(18,38,58,.98), rgba(11,27,44,.98)); border-color: var(--dash-border); }
.dash-kpi:hover, .dash-card:hover, .dash-action:hover { border-color: rgba(94,234,212,.35); }
.dash-stat-card, .dash-list-item, .dash-map-stat, .dash-table th { background: rgba(148,163,184,.07); border-color: var(--dash-border); }
.dash-stat-card:hover, .dash-list-item:hover, .dash-table tr:hover td { background: rgba(45,212,191,.08); }
.dash-progress-track { background: rgba(148,163,184,.14); }
.dash-table td { border-bottom-color: rgba(148,163,184,.10); }
.dash-status-success { background: rgba(16,185,129,.16); color: #6ee7b7; }
.dash-status-warning { background: rgba(245,158,11,.16); color: #fcd34d; }
.dash-status-danger { background: rgba(239,68,68,.16); color: #fca5a5; }
.dash-status-info { background: rgba(34,211,238,.16); color: #67e8f9; }
.dash-status-neutral { background: rgba(148,163,184,.14); color: #cbd5e1; }
.dash-action { background: linear-gradient(145deg, rgba(18,38,58,.98), rgba(11,27,44,.98)); border-color: var(--dash-border); }
.dash-action-title, .dash-card-title, .dash-section-title, .dash-list-title, .dash-stat-value, .dash-map-stat-val { color: var(--dash-text); }
.dash-map-header { background: linear-gradient(125deg, #0a1b2c, #0d4a4b); }
@media (max-width: 640px) { .dash { padding: 1rem; gap: 1rem; } .dash-header { padding: 1.4rem; border-radius: 18px; } .dash-title { font-size: 1.35rem; } .dash-kpi, .dash-card { padding: 1.1rem; } .dash-header-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .dash-map-body { height: 360px; } }
@media (prefers-reduced-motion: reduce) { .dash *, .dash *::before, .dash *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; } }

/* Light dashboard treatment: primary blue, red reserved for priority states. */
.dash { background: var(--dash-bg); }
.dash-header { background: radial-gradient(circle at 85% 0%, rgba(255,255,255,.24), transparent 25%), linear-gradient(125deg, #064981 0%, #0868b9 57%, #0b87d5 100%); border-color: rgba(255,255,255,.26); }
.dash-kpi, .dash-card, .dash-map-container, .dash-action { background: #fff; border-color: var(--dash-border); }
.dash-kpi:hover, .dash-card:hover, .dash-action:hover { border-color: #9fc8ed; box-shadow: var(--dash-shadow-lg); }
.dash-stat-card, .dash-list-item, .dash-map-stat, .dash-table th { background: #f7fbff; border-color: var(--dash-border); }
.dash-stat-card:hover, .dash-list-item:hover, .dash-table tr:hover td { background: #edf6ff; }
.dash-progress-track { background: #e8f1fb; }
.dash-table td { border-bottom-color: #e8f1fb; }
.dash-map-header { background: linear-gradient(125deg, #064981, #0868b9); }
.dash-status-success { background: #e8f7ef; color: #16704a; }
.dash-status-warning { background: #fff5dd; color: #9a6000; }
.dash-status-danger { background: #fff0f1; color: #b01e27; }
.dash-status-info { background: #e3f0ff; color: #075ba7; }
.dash-status-neutral { background: #eef4fa; color: #486581; }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<div class="dash">
    <!-- Header -->
    <header class="dash-header">
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;gap:1rem;align-items:start;">
            <div>
                <div style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.5);margin-bottom:0.35rem;">{{ $dashboard['header']['badge'] }}</div>
                <h1 class="dash-title">{{ $dashboard['header']['title'] }}</h1>
                <p class="dash-subtitle">{{ $dashboard['header']['summary'] }}</p>
            </div>
            <div class="dash-header-actions">
                <span class="dash-badge" style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.2);">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    {{ $dashboard['header']['login_status'] }}
                </span>
            </div>
        </div>
        <div class="dash-header-grid">
            <div class="dash-header-meta"><div class="dash-header-eyebrow">{{ in_array($dashboard['mode'], ['kecamatan','operator'], true) ? 'Kecamatan' : 'Wilayah' }}</div><div class="dash-header-value">{{ $dashboard['header']['scope'] }}</div></div>
            <div class="dash-header-meta"><div class="dash-header-eyebrow">Operator</div><div class="dash-header-value">{{ $dashboard['header']['operator'] }}</div></div>
            <div class="dash-header-meta"><div class="dash-header-eyebrow">Tanggal</div><div class="dash-header-value">{{ $dashboard['header']['date'] }}</div></div>
            <div class="dash-header-meta"><div class="dash-header-eyebrow">Jam Server</div><div class="dash-header-value">{{ $dashboard['header']['time'] }}</div></div>
            <div class="dash-header-meta"><div class="dash-header-eyebrow">Jumlah Desa</div><div class="dash-header-value">{{ $number($dashboard['header']['desa_total']) }}</div></div>
        </div>
    </header>

    <!-- KPI Cards -->
    <section class="dash-kpi-grid">
        @foreach ($dashboard['kpis'] as $kpi)
            @php($color = $colors[$kpi['color']] ?? $colors['gray'])
            <article class="dash-kpi" style="--kpi-color:{{ $color['bar'] }};--kpi-icon-bg:{{ $color['bg'] }};--kpi-icon-color:{{ $color['text'] }};">
                <div class="dash-kpi-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div class="dash-kpi-label">{{ $kpi['label'] }}</div>
                <div class="dash-kpi-value">{{ $number($kpi['value']) }}</div>
                <div class="dash-kpi-desc">{{ $kpi['description'] }}</div>
            </article>
        @endforeach
    </section>

    @if ($dashboard['mode'] === 'kabupaten')
        <!-- Top Cards -->
        <section class="dash-kpi-grid">
            @foreach ($dashboard['topCards'] as $card)
                @php($color = $colors[$card['color']] ?? $colors['gray'])
                <article class="dash-kpi" style="--kpi-color:{{ $color['bar'] }};--kpi-icon-bg:{{ $color['bg'] }};--kpi-icon-color:{{ $color['text'] }};">
                    <div class="dash-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                    <div class="dash-kpi-label">{{ $card['label'] }}</div>
                    <div class="dash-kpi-value">{{ $number($card['value']) }}</div>
                    <div class="dash-kpi-trend" style="color:{{ $color['text'] }};">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                        {{ $card['trend'] }}
                    </div>
                </article>
            @endforeach
        </section>

        <!-- Summary Panels -->
        <section class="dash-split">
            <div class="dash-card">
                <h3 class="dash-section-title">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    Ringkasan Operasional
                </h3>
                <div class="dash-stat-grid">
                    @foreach ($dashboard['summaryPanel'] as $key => $value)
                        <div class="dash-stat-card">
                            <div class="dash-stat-value">{{ $number($value) }}</div>
                            <div class="dash-stat-label">{{ ucwords(str_replace(['_','-'], ' ', $key)) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="dash-card">
                <h3 class="dash-section-title">
                    <svg width="18" height="18" fill="none" stroke="#0d9488" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    Status Verifikasi Batch
                </h3>
                <div class="dash-stat-grid">
                    @foreach ($dashboard['verificationSummary'] as $summary)
                        @php($color = $colors[$summary['color']] ?? $colors['gray'])
                        <div class="dash-stat-card" style="border-top: 3px solid {{ $color['bar'] }};">
                            <div class="dash-stat-value" style="color:{{ $color['text'] }};">{{ $number($summary['count']) }}</div>
                            <div class="dash-stat-label">{{ $summary['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($dashboard['infographics_ready'])
        <!-- Charts -->
        <section>
            <h2 class="dash-section-title">
                <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Visualisasi Data
            </h2>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));">
                @foreach ($dashboard['quickStats'] as $stat)
                    <div class="dash-card">
                        <h3 class="dash-card-title">{{ $stat['title'] }}</h3>
                        <div class="dash-chart-wrap">
                            <canvas id="quickstat-{{ $loop->index }}" data-chart='@json($stat['chart'])' style="width:100%;height:100%;"></canvas>
                        </div>
                    </div>
                @endforeach
                @foreach ($dashboard['chartPanels'] as $panel)
                    <div class="dash-card">
                        <h3 class="dash-card-title">{{ $panel['title'] }}</h3>
                        <div class="dash-chart-wrap">
                            <canvas id="panelchart-{{ $loop->index }}" data-chart='@json($panel['chart'])' style="width:100%;height:100%;"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const palette = @json($chartPalette);
                const render = (canvas) => {
                    if (!canvas) return;
                    try {
                        const d = JSON.parse(canvas.dataset.chart || '{}');
                        const labels = Object.keys(d.data || {});
                        const values = Object.values(d.data || {});
                        const primary = palette[0];
                        const bg = d.type === 'line' ? 'rgba(99,102,241,0.12)' : labels.map((_,i) => palette[i % palette.length]);
                        new Chart(canvas.getContext('2d'), {
                            type: d.type || 'bar',
                            data: { labels, datasets: [{ label: d.label||'Data', data: values, borderColor: primary, backgroundColor: bg, borderWidth: 2, fill: d.type==='line', tension: 0.4, pointRadius: 4, pointHoverRadius: 6, borderRadius: d.type==='bar'?10:0, barPercentage: 0.7, categoryPercentage: 0.75 }] },
                            options: { maintainAspectRatio: false, responsive: true, plugins: { legend: { display: d.type==='doughnut'||d.type==='pie', position: 'bottom', labels: { color: '#64748b', boxWidth: 12, padding: 14 } }, tooltip: { mode: 'index', intersect: false, backgroundColor: 'rgba(15,23,42,0.92)', titleColor: '#f1f5f9', bodyColor: '#f1f5f9' } }, scales: d.type==='line'||d.type==='bar' ? { x: { grid: { display: false }, ticks: { color: '#94a3b8' } }, y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.1)' }, ticks: { color: '#94a3b8' } } } : {} }
                        });
                    } catch (e) { console.warn('Chart error', e); }
                };
                document.querySelectorAll('canvas[data-chart]').forEach(render);
            });
        </script>
        @endif

        <!-- Map -->
        <section class="dash-map-container">
            <div class="dash-map-header">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <div>
                        <h3 style="margin:0;font-size:1.1rem;font-weight:900;color:white;">Peta Digital Pintar</h3>
                        <p style="margin:0.25rem 0 0;color:rgba(255,255,255,0.6);font-size:0.82rem;">Visualisasi rumah verifikasi dan distribusi penduduk di wilayah kabupaten</p>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;min-width:480px;">
                        <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem;text-align:center;">
                            <div style="font-size:1.25rem;font-weight:900;color:white;">{{ number_format(count($dashboard['map']['markers'] ?? [])) }}</div>
                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.5);">Total Titik</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem;text-align:center;">
                            <div style="font-size:1.25rem;font-weight:900;color:white;">{{ number_format($dashboard['map']['total'] ?? 0) }}</div>
                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.5);">Rumah</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem;text-align:center;">
                            <div style="font-size:1.25rem;font-weight:900;color:#fbbf24;">{{ number_format($dashboard['map']['unmapped'] ?? 0) }}</div>
                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.5);">Tanpa Koordinat</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:0.75rem;text-align:center;">
                            <div style="font-size:1.25rem;font-weight:900;color:#34d399;">{{ $dashboard['map']['average_residents'] ?? 0 }}</div>
                            <div style="font-size:0.65rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:rgba(255,255,255,0.5);">Rata-rata</div>
                        </div>
                    </div>
                </div>
                <div style="display:grid;gap:0.75rem;grid-template-columns:1fr auto;">
                    <input id="dash-map-search" type="search" class="dash-map-search" placeholder="Cari desa, alamat, atau kode rumah..." autocomplete="off" />
                    <div style="display:flex;gap:0.5rem;">
                        <select id="dash-map-style" style="padding:0.875rem 1rem;border-radius:12px;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.1);color:white;font-size:0.875rem;font-weight:700;outline:none;">
                            <option value="street">Peta Jalan</option>
                            <option value="satellite">Satelit</option>
                            <option value="topo">Topografi</option>
                        </select>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 260px;">
                <div id="dash-smart-map" class="dash-map-body"></div>
                <div class="dash-map-sidebar" style="border-left:1px solid var(--dash-border);">
                    <div style="grid-column:1/-1;">
                        <h4 style="font-size:0.8rem;font-weight:800;text-transform:uppercase;letter-spacing:0.06em;color:var(--dash-text-muted);margin:0 0 0.75rem 0;">Legenda Status</h4>
                        <div style="display:grid;gap:0.5rem;">
                            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;font-weight:700;color:var(--dash-text-muted);"><span style="width:10px;height:10px;border-radius:50%;background:#10b981;flex-shrink:0;"></span>Terverifikasi</div>
                            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;font-weight:700;color:var(--dash-text-muted);"><span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;flex-shrink:0;"></span>Perlu Verifikasi</div>
                            <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.8rem;font-weight:700;color:var(--dash-text-muted);"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;flex-shrink:0;"></span>Belum Lengkap</div>
                        </div>
                    </div>
                    <div class="dash-map-stat"><div class="dash-map-stat-val">{{ number_format($dashboard['map']['count'] ?? 0) }}</div><div class="dash-map-stat-lbl">Rumah Terpeta</div></div>
                    <div class="dash-map-stat"><div class="dash-map-stat-val">{{ $dashboard['map']['average_residents'] ?? 0 }}</div><div class="dash-map-stat-lbl">Rata-rata Penduduk</div></div>
                    <div id="dash-map-search-results" style="grid-column:1/-1;display:grid;gap:0.5rem;max-height:180px;overflow-y:auto;"></div>
                </div>
            </div>
        </section>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const markers = @json($markers);
                const hasM = Array.isArray(markers) && markers.length > 0;
                const mapEl = document.getElementById('dash-smart-map');
                const map = L.map(mapEl).setView(hasM ? [markers[0].latitude, markers[0].longitude] : [3.6, 125.45], 11);
                const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution:'© OpenStreetMap', maxZoom:19}).addTo(map);
                const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {attribution:'Tiles © Esri', maxZoom:19});
                const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {attribution:'© OpenTopoMap', maxZoom:17});
                let activeBase = street;
                document.getElementById('dash-map-style')?.addEventListener('change', e => { map.removeLayer(activeBase); activeBase = e.target.value === 'satellite' ? satellite : (e.target.value === 'topo' ? topo : street); activeBase.addTo(map); });
                const statusColor = {'Terverifikasi':'#10b981','Perlu Verifikasi':'#f59e0b','Belum Lengkap':'#ef4444'};
                const cluster = L.markerClusterGroup({chunkedLoading:true});
                const markerItems = (hasM ? markers : []).map(m => {
                    const color = statusColor[m.status_rumah] || '#6366f1';
                    const icon = L.divIcon({className:'',html:'<div style="width:24px;height:24px;border-radius:50%;border:3px solid #fff;background:'+color+';box-shadow:0 4px 12px rgba(0,0,0,0.2);display:flex;align-items:center;justify-content:center;"><span style="width:8px;height:8px;background:#fff;border-radius:50%;"></span></div>',iconSize:[24,24],iconAnchor:[12,12]});
                    const mk = L.marker([m.latitude, m.longitude], {icon});
                    mk.bindPopup('<div style="min-width:200px;line-height:1.4;font-family:Inter,sans-serif;"><strong style="font-size:1rem;">'+(m.kode_rumah||'Rumah')+'</strong><div style="color:#64748b;font-size:0.85rem;margin:0.3rem 0;">'+(m.alamat||'Alamat tidak tersedia')+'</div><div style="font-size:0.82rem;color:#334155;">Desa: '+(m.desa||'-')+'</div><div style="font-size:0.82rem;color:#334155;">KK: '+(m.jumlah_kk??0)+' · Penduduk: '+(m.jumlah_penduduk??0)+'</div></div>');
                    mk.searchLabel = (m.kode_rumah||'')+' '+(m.alamat||'')+' '+(m.desa||'').toLowerCase();
                    return {mk, label: mk.searchLabel, data: m};
                });
                if (markerItems.length) { markerItems.forEach(i => cluster.addLayer(i.mk)); map.addLayer(cluster); map.fitBounds(L.featureGroup(markerItems.map(i=>i.mk)).getBounds().pad(0.14)); }
                else { L.marker([3.6,125.45]).addTo(map).bindPopup('Tidak ada data lokasi.'); }
                const srEl = document.getElementById('dash-map-search-results');
                document.getElementById('dash-map-search')?.addEventListener('input', e => {
                    const q = e.target.value.trim().toLowerCase();
                    srEl.innerHTML = '';
                    if (!q) return;
                    markerItems.filter(i => i.label.includes(q)).slice(0,8).forEach(i => {
                        const b = document.createElement('button');
                        b.type = 'button';
                        b.style.cssText = 'width:100%;text-align:left;padding:0.75rem;border-radius:10px;border:1px solid var(--dash-border);background:#f8fafc;cursor:pointer;font-size:0.82rem;transition:background 0.15s;';
                        b.innerHTML = '<strong>'+(i.data.kode_rumah||'Rumah')+'</strong><br><small style="color:#64748b;">'+i.label+'</small>';
                        b.addEventListener('mouseover', () => b.style.background='#f1f5f9');
                        b.addEventListener('mouseout', () => b.style.background='#f8fafc');
                        b.addEventListener('click', () => { map.setView(i.mk.getLatLng(), 16, {animate:true}); i.mk.openPopup(); });
                        srEl.appendChild(b);
                    });
                });
            });
        </script>

        <!-- System Alerts -->
        <section class="dash-split">
            <div class="dash-card">
                <h3 class="dash-section-title">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Sistem & Operasional
                </h3>
                <div class="dash-list">
                    @foreach ($dashboard['systemAlerts'] as $alert)
                        @php($sev = match($alert['severity']){'success'=>$colors['success'],'warning'=>$colors['warning'],'danger'=>$colors['danger'],default=>$colors['gray']})
                        <div class="dash-list-item" style="--list-bar:{{ $sev['bar'] }};">
                            <div>
                                <span class="dash-list-title">{{ $alert['title'] }}</span>
                                <p class="dash-list-desc">{{ $alert['description'] }}</p>
                            </div>
                            <span class="dash-status dash-status-{{ $alert['severity'] }}">{{ $alert['severity'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="dash-card">
                <h3 class="dash-section-title">
                    <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    Kecamatan Prioritas
                </h3>
                <div class="dash-list">
                    @foreach ($dashboard['attentionList'] as $item)
                        @php($color = $colors[$item['color']] ?? $colors['gray'])
                        <div class="dash-list-item" style="--list-bar:{{ $color['bar'] }};">
                            <div>
                                <span class="dash-list-title">{{ $item['label'] }}</span>
                                <p class="dash-list-desc">{{ $item['description'] }}</p>
                            </div>
                            <span class="dash-status dash-status-{{ $item['color'] === 'success' ? 'success' : ($item['color'] === 'warning' ? 'warning' : 'danger') }}">{{ $item['count'] }}{{ $item['suffix'] ?? '%' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (in_array($dashboard['mode'], ['kecamatan', 'operator'], true))
    <!-- Kecamatan/Operator Dashboard -->
    <section class="dash-card">
        <h3 class="dash-section-title">
            <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 000 4h12a2 2 0 000-4M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            {{ $taskCenter['heading'] }}
        </h3>
        <p class="dash-card-subtitle">{{ $taskCenter['description'] }}</p>
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));">
            @foreach ($taskCenter['items'] as $task)
                @php($color = $colors[$task['color']] ?? $colors['gray'])
                <div class="dash-kpi" style="--kpi-color:{{ $color['bar'] }};--kpi-icon-bg:{{ $color['bg'] }};--kpi-icon-color:{{ $color['text'] }};">
                    <span class="dash-status dash-status-{{ $task['color'] === 'success' ? 'success' : ($task['color'] === 'warning' ? 'warning' : 'danger') }}">{{ $task['status'] }}</span>
                    <div class="dash-kpi-label">{{ $task['label'] }}</div>
                    @if ($task['available'])
                        <div class="dash-kpi-value" style="font-size:1.75rem;">{{ $number($task['count']) }}{{ $task['suffix'] ?? '' }}</div>
                    @else
                        <div style="color:var(--dash-text-muted);font-size:0.8rem;font-weight:700;margin-top:0.5rem;">Belum tersedia</div>
                    @endif
                    <div class="dash-kpi-desc">{{ $task['description'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="dash-split">
        <div class="dash-card">
            <h3 class="dash-section-title">
                <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Progress Pendataan
            </h3>
            <div class="dash-progress">
                @foreach ($progressItems as $item)
                    @php($color = $colors[$item['color']] ?? $colors['gray'])
                    <div class="dash-progress-item">
                        <div class="dash-progress-head">
                            <span class="dash-progress-label">{{ $item['label'] }}</span>
                            <span class="dash-progress-val">{{ $number($item['done']) }} / {{ $number($item['total']) }} — {{ $item['percent'] }}%</span>
                        </div>
                        <div class="dash-progress-track">
                            <div class="dash-progress-fill" style="width:{{ $item['percent'] }}%;background:{{ $color['bar'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="dash-card">
            <h3 class="dash-section-title">
                <svg width="18" height="18" fill="none" stroke="#d97706" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Progress per Desa
            </h3>
            <div class="dash-list">
                @forelse ($villageProgress as $village)
                    @php($color = $colors[$village['color']] ?? $colors['gray'])
                    <div class="dash-list-item" style="--list-bar:{{ $color['bar'] }};">
                        <div>
                            <span class="dash-list-title">{{ $village['nama'] }}</span>
                            <p class="dash-list-desc">{{ $number($village['rumah']) }} rumah · {{ $number($village['keluarga']) }} KK · {{ $number($village['penduduk']) }} penduduk</p>
                        </div>
                        <span class="dash-status dash-status-{{ $village['color'] === 'success' ? 'success' : 'warning' }}">{{ $village['progress'] }}%</span>
                    </div>
                @empty
                    <p style="color:var(--dash-text-muted);font-size:0.875rem;">Belum ada data desa.</p>
                @endforelse
            </div>
        </div>
    </section>

    @if (!empty($charts))
    <section>
        <h2 class="dash-section-title">
            <svg width="18" height="18" fill="none" stroke="#8b5cf6" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Statistik
        </h2>
        <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
            @php($ci = 0)
            @foreach ($charts as $chart)
                <div class="dash-card">
                    <h3 class="dash-card-title">{{ $chart['label'] }}</h3>
                    <div class="dash-chart-wrap">
                        <canvas id="dash-chart-{{ $ci }}" style="max-height:280px;width:100%;"></canvas>
                    </div>
                </div>
                @php($ci++)
            @endforeach
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const charts = @json(array_values($charts));
            const palette = @json($chartPalette);
            charts.forEach((chart, i) => {
                const canvas = document.getElementById('dash-chart-' + i);
                if (!canvas || typeof Chart === 'undefined') return;
                const labels = Object.keys(chart.data || {});
                const values = Object.values(chart.data || {}).map(Number);
                const type = chart.type === 'doughnut' || chart.type === 'pie' ? chart.type : (chart.type || 'bar');
                new Chart(canvas.getContext('2d'), {
                    type,
                    data: { labels, datasets: [{ label: chart.label||'Data', data: values, borderWidth: 2, borderRadius: type==='bar'?8:0, backgroundColor: type==='pie'||type==='doughnut' ? palette : palette[0], borderColor: '#ffffff' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: type==='pie'||type==='doughnut', position: 'bottom' }, tooltip: { backgroundColor: 'rgba(15,23,42,0.92)', titleColor: '#f1f5f9', bodyColor: '#f1f5f9' } }, scales: type==='pie'||type==='doughnut' ? {} : { y: { beginAtZero: true, ticks: { color: '#94a3b8' } }, x: { grid: { display: false }, ticks: { color: '#94a3b8' } } } }
                });
            });
        });
    </script>
    @endif
    @endif

    @if ($dashboard['mode'] === 'kabupaten')
    <section class="dash-card">
        <h3 class="dash-section-title">
            <svg width="18" height="18" fill="none" stroke="#14b8a6" stroke-width="2"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ $dashboard['monitoring']['heading'] }}
        </h3>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Nama Kecamatan</th>
                        <th>Rumah</th><th>KK</th><th>Penduduk</th>
                        <th>Progress</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dashboard['monitoring']['rows'] as $row)
                        @php($color = $colors[$row['color']] ?? $colors['gray'])
                        <tr>
                            <td><strong>{{ $row['nama'] }}</strong></td>
                            <td>{{ $number($row['rumah']) }}</td>
                            <td>{{ $number($row['keluarga']) }}</td>
                            <td>{{ $number($row['penduduk']) }}</td>
                            <td><strong>{{ $row['progress'] }}%</strong></td>
                            <td><span class="dash-status dash-status-{{ $row['color'] === 'success' ? 'success' : ($row['color'] === 'warning' ? 'warning' : 'danger') }}">{{ $row['status'] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--dash-text-muted);">Belum ada data kecamatan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    @endif

    <!-- Quick Actions -->
    <section>
        <h2 class="dash-section-title">
            <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 3 14 13 2"/></svg>
            Aksi Cepat
        </h2>
        <div class="dash-action-grid">
            @foreach ($dashboard['quickActions'] as $action)
                @php($color = $colors[$action['color']] ?? $colors['gray'])
                <a class="dash-action" href="{{ $action['url'] }}" style="--kpi-icon-bg:{{ $color['bg'] }};">
                    <div class="dash-action-icon" style="background:{{ $color['bg'] }};color:{{ $color['text'] }};">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    </div>
                    <div>
                        <div class="dash-action-title">{{ $action['label'] }}</div>
                        <div class="dash-action-desc">{{ $action['description'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</div>
</x-filament::page>
