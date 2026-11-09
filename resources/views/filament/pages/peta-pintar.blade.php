<x-filament-panels::page>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

    <style>
        .pp-wrapper {
            --pp-bg: #ffffff; --pp-surface: #f8fafc; --pp-border: #e2e8f0; --pp-text: #0f172a;
            --pp-muted: #64748b; --pp-accent: #059669; --pp-accent-hover: #047857;
            --pp-radius: 0.75rem; --pp-radius-lg: 1rem;
            --pp-shadow: 0 1px 3px rgba(0,0,0,0.06),0 1px 2px rgba(0,0,0,0.04);
            --pp-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07),0 2px 4px -1px rgba(0,0,0,0.04);
            --pp-shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.08),0 4px 6px -2px rgba(0,0,0,0.04);
            --pp-shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.08),0 10px 10px -5px rgba(0,0,0,0.03);
            display: flex; flex-direction: column; gap: 1.25rem; padding-bottom: 1.5rem;
        }
        .dark .pp-wrapper { --pp-bg: #0f172a; --pp-surface: #1e293b; --pp-border: #334155; --pp-text: #f1f5f9; --pp-muted: #94a3b8; }

        /* HEADER */
        .pp-header {
            background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0f172a 100%);
            border: 1px solid rgba(255,255,255,0.06); border-radius: var(--pp-radius-lg);
            padding: 2rem 2rem 1.75rem; position: relative; overflow: hidden; box-shadow: var(--pp-shadow-lg);
        }
        .pp-header::before { content:""; position: absolute; top: 0; right: 0; width: 300px; height: 100%; background: radial-gradient(ellipse at right center,rgba(5,150,105,0.12) 0%,transparent 70%); pointer-events: none; }
        .pp-header::after { content:""; position: absolute; bottom: 0; left: 0; right: 0; height: 1px; background: linear-gradient(90deg,transparent,rgba(5,150,105,0.4),transparent); }
        .pp-header-inner { position: relative; z-index: 1; }
        .pp-header-title { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
        .pp-header-icon { width: 44px; height: 44px; background: rgba(5,150,105,0.15); border: 1px solid rgba(5,150,105,0.3); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .pp-header-icon svg { width: 22px; height: 22px; color: #34d399; }
        .pp-header h1 { font-size: 1.5rem; font-weight: 800; color: #ffffff; letter-spacing: -0.02em; line-height: 1.2; margin: 0; }
        .pp-header-subtitle { font-size: 0.875rem; color: rgba(255,255,255,0.5); font-weight: 400; margin-left: calc(44px + 0.75rem); letter-spacing: 0.01em; }
        .pp-header-meta { display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem; margin-left: calc(44px + 0.75rem); flex-wrap: wrap; }
        .pp-meta-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.75rem; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 9999px; font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.7); letter-spacing: 0.02em; }
        .pp-meta-badge svg { width: 12px; height: 12px; }

        /* CONTROLS */
        .pp-controls { display: grid; grid-template-columns: 1.5fr repeat(4,1fr) 1.2fr; gap: 0.875rem; align-items: stretch; }
        @media(max-width:1440px){.pp-controls{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @media(max-width:768px){.pp-controls{grid-template-columns:1fr}}
        .pp-control-card { background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius); padding: 1rem 1.125rem; box-shadow: var(--pp-shadow); transition: box-shadow 0.2s ease,border-color 0.2s ease; }
        .pp-control-card:hover { box-shadow: var(--pp-shadow-md); border-color: rgba(5,150,105,0.2); }
        .pp-control-label { display: flex; align-items: center; gap: 0.4rem; font-size: 0.6875rem; font-weight: 700; color: var(--pp-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.625rem; }
        .pp-control-label svg { width: 13px; height: 13px; flex-shrink: 0; }
        .pp-control-select,.pp-search-input { width: 100%; padding: 0.625rem 0.875rem; background: var(--pp-surface); border: 1.5px solid var(--pp-border); border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; color: var(--pp-text); transition: border-color 0.2s,box-shadow 0.2s; appearance: none; -webkit-appearance: none; cursor: pointer; outline: none; }
        .pp-control-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; padding-right: 2.25rem; }
        .pp-control-select:focus,.pp-search-input:focus { border-color: var(--pp-accent); box-shadow: 0 0 0 3px rgba(5,150,105,0.12); background-color: var(--pp-bg); }
        .dark .pp-control-select,.dark .pp-search-input { background-color: #0f172a; color: #f1f5f9; }
        .pp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; width: 100%; padding: 0.625rem 1rem; background: var(--pp-accent); color: #ffffff; border: none; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 700; cursor: pointer; transition: background 0.2s,transform 0.15s,box-shadow 0.2s; box-shadow: 0 1px 2px rgba(5,150,105,0.3); letter-spacing: 0.01em; }
        .pp-btn:hover { background: var(--pp-accent-hover); box-shadow: 0 4px 12px rgba(5,150,105,0.3); transform: translateY(-1px); }
        .pp-btn:active { transform: translateY(0); }
        .pp-btn svg { width: 14px; height: 14px; }
        .pp-btn-sm { padding: 0.5rem 0.75rem; background: var(--pp-surface); color: var(--pp-muted); border: 1px solid var(--pp-border); box-shadow: none; font-weight: 600; }
        .pp-btn-sm:hover { background: var(--pp-border); color: var(--pp-text); box-shadow: none; transform: none; }
        .pp-action-row { display: flex; gap: 0.5rem; }
        .pp-action-row .pp-btn,.pp-action-row .pp-btn-sm { flex: 1; }
        .pp-search-wrapper { position: relative; }
        .pp-search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--pp-muted); pointer-events: none; }
        .pp-search-input { padding-left: 2.25rem; }
        .pp-search-results { position: absolute; top: calc(100% + 4px); left: 0; right: 0; background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius); box-shadow: var(--pp-shadow-xl); z-index: 100; max-height: 320px; overflow-y: auto; display: none; }
        .pp-search-results.is-open { display: block; }
        .pp-search-item { padding: 0.75rem 1rem; border-bottom: 1px solid var(--pp-border); cursor: pointer; transition: background 0.15s; }
        .pp-search-item:last-child { border-bottom: none; }
        .pp-search-item:hover { background: var(--pp-surface); }
        .pp-search-item-name { font-size: 0.8125rem; font-weight: 700; color: var(--pp-text); margin-bottom: 0.2rem; }
        .pp-search-item-meta { font-size: 0.75rem; color: var(--pp-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pp-search-empty { padding: 1.25rem 1rem; text-align: center; font-size: 0.8125rem; color: var(--pp-muted); }

        /* MAIN LAYOUT */
        .pp-main { display: grid; grid-template-columns: 1fr 280px; gap: 1rem; align-items: start; }
        @media(max-width:1024px){.pp-main{grid-template-columns:1fr}}
        .pp-map-container { background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius-lg); overflow: hidden; box-shadow: var(--pp-shadow-lg); position: relative; }
        .pp-map-container #peta-pintar { height: 58vh; min-height: 480px; }
        .pp-map-overlay { position: absolute; top: 0.75rem; right: 0.75rem; z-index: 400; display: flex; flex-direction: column; gap: 0.375rem; }
        .pp-map-btn { width: 36px; height: 36px; background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--pp-shadow-md); transition: all 0.15s; color: var(--pp-muted); }
        .pp-map-btn:hover { background: var(--pp-surface); border-color: var(--pp-accent); color: var(--pp-accent); }
        .pp-map-btn svg { width: 16px; height: 16px; }
        .pp-map-btn { position:relative; }
        .pp-map-btn[data-tip]:hover::after { content:attr(data-tip); position:absolute; right:calc(100% + 8px); white-space:nowrap; background:#0f172a; color:white; border-radius:6px; padding:.35rem .55rem; font-size:.7rem; font-weight:700; }
        .pp-feature-pin { background:transparent!important; border:0!important; }
        .pp-feature-pin svg { display:block; filter:drop-shadow(0 2px 3px rgba(15,23,42,.38)); transform-origin:50% 90%; transition:transform .16s ease,filter .16s ease; }
        .pp-feature-pin:hover svg { transform:translateY(-2px) scale(1.1); filter:drop-shadow(0 4px 5px rgba(15,23,42,.38)); }
        .pp-feature-popup { width:min(360px,calc(100vw - 64px)); max-height:min(58vh,440px); overflow:auto; background:#fff; color:#0f172a; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
        .pp-feature-popup__header { padding:15px 44px 13px 16px; background:linear-gradient(135deg,#0f172a,#164e63); color:#fff; }
        .pp-feature-popup__eyebrow { margin-bottom:4px; color:#99f6e4; font-size:9px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }
        .pp-feature-popup__title { display:block; font-size:15px; font-weight:800; line-height:1.35; overflow-wrap:anywhere; }
        .pp-feature-popup__meta { margin-top:5px; color:#cbd5e1; font-size:11px; line-height:1.45; }
        .pp-feature-popup__notice { margin:10px 12px 0; padding:8px 10px; border:1px solid #e9d5ff; border-radius:8px; background:#faf5ff; color:#6b21a8; font-size:10px; font-weight:800; line-height:1.4; }
        .pp-feature-popup__rows { padding:6px 14px 12px; }
        .pp-feature-row { display:grid; grid-template-columns:minmax(90px,.8fr) minmax(0,1.2fr); gap:12px; padding:9px 2px; border-bottom:1px solid #e9eef5; font-size:12px; line-height:1.45; }
        .pp-feature-row:last-child { border-bottom:0; }
        .pp-feature-row span { color:#64748b; overflow-wrap:anywhere; }
        .pp-feature-row strong { color:#0f172a; font-weight:700; text-align:right; overflow-wrap:anywhere; }
        .pp-feature-popup__empty { padding:16px; color:#64748b; font-size:12px; }
        .pp-district-popup { width:min(390px,calc(100vw - 64px)); max-height:min(62vh,500px); overflow:auto; background:#fff; color:#0f172a; font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
        .pp-district-popup__header { padding:16px 48px 14px 16px; color:#fff; background:linear-gradient(135deg,#0f172a,#115e59); }
        .pp-district-popup__eyebrow { color:#99f6e4; font-size:9px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }
        .pp-district-popup__title { display:block; margin-top:3px; font-size:19px; font-weight:850; line-height:1.25; }
        .pp-district-popup__meta { margin-top:5px; color:#cbd5e1; font-size:11px; }
        .pp-district-popup__content { padding:12px 14px 16px; }
        .pp-district-kpis { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:13px; }
        .pp-district-kpi { padding:10px; border:1px solid #e2e8f0; border-radius:9px; background:#f8fafc; }
        .pp-district-kpi strong { display:block; color:#0f172a; font-size:18px; font-weight:850; line-height:1.2; }
        .pp-district-kpi span { display:block; margin-top:3px; color:#64748b; font-size:10px; font-weight:700; }
        .pp-district-section { margin-top:12px; }
        .pp-district-section__title { margin-bottom:6px; color:#0f766e; font-size:10px; font-weight:850; letter-spacing:.08em; text-transform:uppercase; }
        .pp-district-list { border:1px solid #e7edf4; border-radius:9px; overflow:hidden; }
        .pp-district-row { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; padding:9px 10px; border-bottom:1px solid #e7edf4; }
        .pp-district-row:last-child { border-bottom:0; }
        .pp-district-row__name { color:#1e293b; font-size:11px; font-weight:750; line-height:1.35; }
        .pp-district-row__meta { margin-top:3px; color:#64748b; font-size:9px; line-height:1.35; }
        .pp-district-row__value { align-self:center; color:#0f172a; font-size:12px; font-weight:850; text-align:right; white-space:nowrap; }
        .pp-district-empty { padding:11px; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b; font-size:11px; line-height:1.5; }
        .leaflet-interactive.pp-route-demo { stroke-dasharray:7 7; stroke-linecap:round; stroke-linejoin:round; }
        .leaflet-interactive.pp-route-published { stroke-linecap:round; stroke-linejoin:round; }
        .pp-thematic-legend { display:none; position:absolute; z-index:500; left:1rem; bottom:1rem; width:min(300px,calc(100% - 2rem)); padding:.85rem 1rem; background:rgba(255,255,255,.96); border:1px solid #dbe4ee; border-radius:12px; box-shadow:0 8px 24px rgba(15,23,42,.16); color:#0f172a; }
        .pp-thematic-legend.is-visible { display:block; }
        .pp-thematic-gradient { height:12px; margin:.6rem 0 .3rem; border-radius:999px; background:linear-gradient(90deg,#dbeafe,#60a5fa,#1d4ed8,#1e3a8a); }
        .pp-thematic-range { display:flex; justify-content:space-between; color:#64748b; font-size:.7rem; }
        .pp-theme-caption { color:var(--pp-muted); font-size:.72rem; margin-top:.5rem; line-height:1.4; }

        /* STATS */
        .pp-stats-panel { display: flex; flex-direction: column; gap: 0.75rem; }
        .pp-stat-card { background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius); padding: 1rem 1.125rem; box-shadow: var(--pp-shadow); transition: box-shadow 0.2s; }
        .pp-stat-card:hover { box-shadow: var(--pp-shadow-md); }
        .pp-stat-card-title { display: flex; align-items: center; gap: 0.4rem; font-size: 0.6875rem; font-weight: 700; color: var(--pp-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; }
        .pp-stat-card-title svg { width: 13px; height: 13px; }
        .pp-stat-items { display: flex; flex-direction: column; gap: 0.5rem; }
        .pp-stat-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; background: var(--pp-surface); border-radius: 0.5rem; border: 1px solid var(--pp-border); }
        .pp-stat-dot { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .pp-stat-dot svg { width: 18px; height: 18px; color: white; }
        .pp-stat-dot--houses { background: linear-gradient(135deg,#3b82f6,#1d4ed8); }
        .pp-stat-dot--families { background: linear-gradient(135deg,#f59e0b,#d97706); }
        .pp-stat-dot--people { background: linear-gradient(135deg,#059669,#047857); }
        .pp-stat-num { font-size: 1.375rem; font-weight: 800; color: var(--pp-text); line-height: 1; letter-spacing: -0.02em; }
        .pp-stat-label { font-size: 0.6875rem; font-weight: 600; color: var(--pp-muted); text-transform: uppercase; letter-spacing: 0.06em; margin-top: 0.15rem; }
        .pp-stat-right { flex: 1; }

        /* DETAIL */
        .pp-detail-card { background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius); padding: 1rem 1.125rem; box-shadow: var(--pp-shadow); transition: all 0.3s; display: none; }
        .pp-detail-card.is-visible { display: block; border-color: rgba(5,150,105,0.3); background: linear-gradient(135deg,rgba(5,150,105,0.03),var(--pp-bg)); }
        .pp-detail-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
        .pp-detail-title { display: flex; align-items: center; gap: 0.4rem; font-size: 0.6875rem; font-weight: 700; color: var(--pp-accent); text-transform: uppercase; letter-spacing: 0.08em; }
        .pp-detail-title svg { width: 13px; height: 13px; }
        .pp-detail-close { width: 24px; height: 24px; border-radius: 6px; border: 1px solid var(--pp-border); background: var(--pp-surface); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--pp-muted); transition: all 0.15s; }
        .pp-detail-close:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
        .pp-detail-close svg { width: 12px; height: 12px; }
        .pp-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 0.75rem; }
        .pp-detail-item-label { font-size: 0.6875rem; font-weight: 600; color: var(--pp-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.15rem; }
        .pp-detail-item-value { font-size: 0.8125rem; font-weight: 600; color: var(--pp-text); word-break: break-word; }
        .pp-detail-divider { grid-column: 1 / -1; height: 1px; background: var(--pp-border); margin: 0.25rem 0; }
        .pp-detail-badge { display: inline-flex; align-items: center; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.02em; }
        .pp-detail-badge--success { background: #d1fae5; color: #065f46; }
        .pp-detail-badge--warning { background: #fef3c7; color: #92400e; }
        .pp-detail-badge--danger { background: #fee2e2; color: #991b1b; }
        .dark .pp-detail-badge--success { background: rgba(5,150,105,0.2); color: #34d399; }
        .dark .pp-detail-badge--warning { background: rgba(245,158,11,0.2); color: #fbbf24; }
        .dark .pp-detail-badge--danger { background: rgba(239,68,68,0.2); color: #f87171; }

        /* LEGEND */
        .pp-legend { background: var(--pp-bg); border: 1px solid var(--pp-border); border-radius: var(--pp-radius-lg); padding: 1.25rem 1.5rem; box-shadow: var(--pp-shadow); }
        .pp-legend-grid { display: none; }
        .pp-legend-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--pp-border); }
        .pp-legend-icon { width: 28px; height: 28px; background: linear-gradient(135deg,#0f172a,#1e3a5f); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
        .pp-legend-icon svg { width: 14px; height: 14px; color: #34d399; }
        .pp-legend-title { font-size: 0.8125rem; font-weight: 800; color: var(--pp-text); letter-spacing: 0.01em; }
        .pp-legend-subtitle { font-size: 0.6875rem; color: var(--pp-muted); margin-top: 0.1rem; }
        .pp-legend-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 0.75rem; }
        @media(max-width:640px){.pp-legend-grid{grid-template-columns:repeat(2,1fr)}}
        .pp-legend-item { display: flex; align-items: center; gap: 0.625rem; }
        .pp-legend-marker { width: 32px; height: 22px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(15,23,42,0.16); }
        .pp-legend-marker svg { display:none; }
        .pp-legend-marker--high { background: #1e3a8a; }
        .pp-legend-marker--medium { background: #93c5fd; }
        .pp-legend-marker--low { background: #cbd5e1; }
        .pp-legend-marker--cluster { width: 16px; height: 16px; margin: 3px 8px; border-radius: 50%; background: #f97316; }
        .pp-legend-text-primary { font-size: 0.8125rem; font-weight: 700; color: var(--pp-text); line-height: 1.2; }
        .pp-legend-text-secondary { font-size: 0.6875rem; color: var(--pp-muted); margin-top: 0.1rem; }

        /* LEAFLET OVERRIDES */
        .leaflet-popup-content-wrapper { border-radius: 14px !important; box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important; border: 1px solid rgba(226,232,240,0.8) !important; overflow: hidden !important; padding: 0 !important; }

        /* MOBILE RESPONSIVE — Peta Pintar Admin */
        @media (max-width: 1024px) {
            .pp-main { grid-template-columns: 1fr; }
            .pp-stats-panel { order: -1; }
            .pp-stat-items { flex-direction: row; flex-wrap: wrap; }
            .pp-stat-item { flex: 1 1 calc(50% - 0.25rem); min-width: 140px; }
        }
        @media (max-width: 768px) {
            .pp-wrapper { padding: 0.75rem; gap: 0.75rem; }
            .pp-header { padding: 1.25rem; }
            .pp-header h1 { font-size: 1.125rem; }
            .pp-header-subtitle { margin-left: 0; font-size: 0.75rem; margin-top: 0.5rem; }
            .pp-header-meta { margin-left: 0; margin-top: 0.75rem; }
            .pp-map-container #peta-pintar { height: 45vh; min-height: 300px; }
            .pp-map-overlay { top: 0.5rem; right: 0.5rem; }
            .pp-map-btn { width: 32px; height: 32px; }
            .pp-legend { padding: 1rem; }
            .pp-legend-grid { grid-template-columns: repeat(2, 1fr); }
            .pp-controls { grid-template-columns: 1fr; gap: 0.5rem; }
            .pp-stat-items { flex-direction: column; }
            .pp-stat-item { flex: none; }
        }
        @media (max-width: 480px) {
            .pp-wrapper { padding: 0.5rem; }
            .pp-header { padding: 1rem; border-radius: 0.625rem; }
            .pp-header-icon { width: 36px; height: 36px; }
            .pp-header-icon svg { width: 18px; height: 18px; }
            .pp-header h1 { font-size: 1rem; }
            .pp-map-container { border-radius: 0.625rem; }
            .pp-detail-card { padding: 0.875rem; }
        }
        .dark .leaflet-popup-content-wrapper { background: #1e293b; border-color: #334155 !important; }
        .leaflet-popup-content { margin: 0 !important; width: auto !important; }
        .leaflet-popup-close-button { top: 10px !important; right: 10px !important; width: 28px !important; height: 28px !important; padding: 0 !important; font-size: 20px !important; line-height: 28px !important; text-align: center !important; border-radius: 50% !important; background: rgba(241,245,249,0.9) !important; color: #64748b !important; border: none !important; font-weight: 300 !important; }
        .leaflet-popup-close-button:hover { background: #ef4444 !important; color: white !important; }
        .leaflet-popup-tip-container { display: none !important; }
        .pp-search-results::-webkit-scrollbar { width: 4px; }
        .pp-search-results::-webkit-scrollbar-thumb { background: var(--pp-border); border-radius: 4px; }
    </style>    <div class="pp-wrapper">

        <!-- HEADER -->
        <div class="pp-header">
            <div class="pp-header-inner">
                <div class="pp-header-title">
                    <div class="pp-header-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                        </svg>
                    </div>
                    <h1>{{ $this->getTitle() }}</h1>
                </div>
                <p class="pp-header-subtitle">Peta tematik per kecamatan dan sebaran lokasi sektoral dari data yang sudah diterbitkan.</p>
                <div class="pp-header-meta">
                    <span class="pp-meta-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        Peta Interaktif
                    </span>
                    <span class="pp-meta-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" /></svg>
                        Data OPD Terbit
                    </span>
                    <span class="pp-meta-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        Filter OPD dan dataset
                    </span>
                </div>
            </div>
        </div>

        <!-- CONTROLS -->
        <div class="pp-controls">
            <div class="pp-control-card">
                <label class="pp-control-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    Cari lokasi atau data
                </label>
                <div class="pp-search-wrapper">
                    <svg class="pp-search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <input type="text" id="pp-search" class="pp-search-input" placeholder="Nama objek, OPD, atau dataset..." autocomplete="off">
                    <div id="pp-search-results" class="pp-search-results"></div>
                </div>
            </div>
            <div class="pp-control-card">
                <label class="pp-control-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    OPD
                </label>
                <select id="pp-filter-desa" class="pp-control-select">
                    <option value="">Semua OPD</option>
                </select>
            </div>
            <div class="pp-control-card">
                <label class="pp-control-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    Dataset lokasi
                </label>
                <select id="pp-filter-density" class="pp-control-select">
                    <option value="">Semua dataset</option>
                </select>
            </div>
            <div class="pp-control-card">
                <label class="pp-control-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Status
                </label>
                <select id="pp-filter-disease" class="pp-control-select">
                    <option value="all">Semua data</option>
                    <option value="published">Data terbit</option>
                    <option value="demo">Data demo</option>
                </select>
            </div>
            <div class="pp-control-card">
                <label class="pp-control-label" for="pp-indicator">
                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 14l4-4 3 3 6-7" /></svg>
                    Indikator per kecamatan
                </label>
                <select id="pp-indicator" class="pp-control-select">
                    <option value="">Pilih indikator terbit</option>
                </select>
                <div class="pp-theme-caption">Warna poligon mengikuti jumlah nilai indikator yang diterbitkan.</div>
            </div>
            <div class="pp-control-card">
                <label class="pp-control-label">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                    Mode Peta
                </label>
                <select id="pp-map-mode" class="pp-control-select">
                    <option value="osm">Street Map</option>
                    <option value="satellite">Satellite</option>
                    <option value="topo">Topografi</option>
                </select>
                <div class="pp-action-row" style="margin-top:0.625rem;">
                    <button type="button" id="pp-fit-bounds" class="pp-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" /></svg>
                        <span>Fokus peta</span>
                    </button>
                    <button type="button" id="pp-clear" class="pp-btn pp-btn-sm" aria-label="Atur ulang filter peta" title="Atur ulang filter peta">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>        <!-- MAP + STATS -->
        <div class="pp-main">
            <div class="pp-map-container">
                <div id="peta-pintar"></div>
                <div id="pp-thematic-legend" class="pp-thematic-legend" aria-live="polite">
                    <div id="pp-legend-title" style="font-size:.8rem;font-weight:800;">Sebaran indikator</div>
                    <div id="pp-legend-description" class="pp-theme-caption"></div>
                    <div class="pp-thematic-gradient"></div>
                    <div class="pp-thematic-range"><span id="pp-legend-min">0</span><span id="pp-legend-max">0</span></div>
                </div>
                <div class="pp-map-overlay">
                    <button class="pp-map-btn" id="pp-zoom-in" data-tip="Perbesar peta" aria-label="Perbesar peta">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </button>
                    <button class="pp-map-btn" id="pp-zoom-out" data-tip="Perkecil peta" aria-label="Perkecil peta">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" /></svg>
                    </button>
                    <button class="pp-map-btn" id="pp-locate" data-tip="Lokasi saya" aria-label="Lokasi saya">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                    </button>
                </div>
            </div>
            <div class="pp-stats-panel">
                <div class="pp-stat-card">
                    <div class="pp-stat-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" /></svg>
                        Ringkasan layer peta
                    </div>
                    <div class="pp-stat-items">
                        <div class="pp-stat-item">
                            <div class="pp-stat-dot pp-stat-dot--houses">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.4 7-11a7 7 0 10-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.2"/></svg>
                            </div>
                            <div class="pp-stat-right">
                                <div class="pp-stat-num" id="pp-total-rumah">0</div>
                                <div class="pp-stat-label">Fitur pada peta</div>
                            </div>
                        </div>
                        <div class="pp-stat-item">
                            <div class="pp-stat-dot pp-stat-dot--families">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/><circle cx="7" cy="6" r="1" fill="currentColor"/><circle cx="7" cy="12" r="1" fill="currentColor"/><circle cx="7" cy="18" r="1" fill="currentColor"/></svg>
                            </div>
                            <div class="pp-stat-right">
                                <div class="pp-stat-num" id="pp-total-kk">0</div>
                                <div class="pp-stat-label">Dataset tampil</div>
                            </div>
                        </div>
                        <div class="pp-stat-item">
                            <div class="pp-stat-dot pp-stat-dot--people">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V6l7-3 7 3v15M9 9h.01M15 9h.01M9 13h.01M15 13h.01M10 21v-4h4v4"/></svg>
                            </div>
                            <div class="pp-stat-right">
                                <div class="pp-stat-num" id="pp-total-penduduk">0</div>
                                <div class="pp-stat-label">OPD tampil</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pp-detail-card is-visible" id="pp-detail-card" style="display:block">
                    <div class="pp-detail-header">
                        <div class="pp-detail-title">
                            Data peta
                        </div>
                    </div>
                    <div id="pp-detail-content">Klik titik, garis, atau area pada peta untuk melihat OPD, dataset, periode, dan ringkasan nilainya. Gunakan filter untuk mempersempit tampilan.</div>
                </div>
            </div>
        </div>

        <!-- LEGEND -->
        <div class="pp-legend">
            <div class="pp-legend-header">
                <div class="pp-legend-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>
                </div>
                <div>
                    <div class="pp-legend-title">Cara membaca peta</div>
                    <div class="pp-legend-subtitle">Batas kecamatan dipadukan dengan nilai indikator terbit dan lokasi sektoral.</div>
                </div>
            </div>
            <div class="pp-legend-grid">
                <div class="pp-legend-item">
                    <div class="pp-legend-marker pp-legend-marker--high">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                    <div><div class="pp-legend-text-primary">Nilai tinggi</div><div class="pp-legend-text-secondary">Warna biru tua</div></div>
                </div>
                <div class="pp-legend-item">
                    <div class="pp-legend-marker pp-legend-marker--medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                    <div><div class="pp-legend-text-primary">Nilai lebih rendah</div><div class="pp-legend-text-secondary">Warna biru muda</div></div>
                </div>
                <div class="pp-legend-item">
                    <div class="pp-legend-marker pp-legend-marker--low">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    </div>
                    <div><div class="pp-legend-text-primary">Belum ada nilai</div><div class="pp-legend-text-secondary">Warna abu-abu</div></div>
                </div>
                <div class="pp-legend-item">
                    <div class="pp-legend-marker pp-legend-marker--cluster">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                    </div>
                    <div><div class="pp-legend-text-primary">Lokasi sektoral</div><div class="pp-legend-text-secondary">Titik dan jalur infrastruktur</div></div>
                </div>
            </div>
        </div>

    </div>    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        'use strict';

        const markersData = @json($this->markers);
        const spatialData = @json($this->dataset_spatial_features);
        const thematicData = @json($this->thematic_map_data);
        const first = markersData[0] || { latitude: 3.6247, longitude: 125.5000 };

        // MAP INIT
        const map = L.map('peta-pintar', {
            center: [first.latitude, first.longitude],
            zoom: 13,
            zoomControl: false,
        });

        // BASE LAYERS
        const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19,
            attribution: '&copy; Esri'
        });

        const topo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '&copy; OpenTopoMap'
        });

        let currentLayer = osm;

        // MARKER ICONS
        const homeSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>';

        const mkIcon = (c) => L.divIcon({
            className: 'pp-mi',
            html: `<div style="width:38px;height:38px;background:${c};border:3px solid #fff;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.3),inset 0 1px 0 rgba(255,255,255,0.25);">${homeSVG}</div>`,
            iconSize: [38, 38],
            iconAnchor: [19, 19],
            popupAnchor: [0, -22],
        });

        const icons = { high: mkIcon('#ef4444'), medium: mkIcon('#f97316'), low: mkIcon('#22c55e'), default: mkIcon('#3b82f6') };

        // CLUSTER
        const mcg = L.markerClusterGroup({
            maxClusterRadius: 60,
            disableClusteringAtZoom: 18,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            iconCreateFunction: (cl) => {
                const n = cl.getChildCount();
                const s = n < 10 ? 44 : n < 50 ? 52 : 60;
                const f = n < 10 ? '12px' : n < 50 ? '13px' : '11px';
                const c = n >= 25 ? '#ef4444' : n >= 10 ? '#f97316' : '#3b82f6';
                return L.divIcon({
                    html: `<div style="width:${s}px;height:${s}px;background:${c};border:3px solid #fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:${f};font-weight:800;color:white;box-shadow:0 4px 14px rgba(0,0,0,0.3);letter-spacing:-0.02em;">${n}</div>`,
                    className: 'pp-cl',
                    iconSize: L.point(s, s),
                    iconAnchor: L.point(s / 2, s / 2),
                });
            }
        });

        const esc = (v) => String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');

        const buildPopup = (r) => {
            const [bg,fc] = {'milik sendiri':['#d1fae5','#065f46'],'kontrak':['#fef3c7','#92400e'],'menyewa':['#fef3c7','#92400e'],'bebas':['#fee2e2','#991b1b']}[r.status_rumah?.toLowerCase()] || ['#d1fae5','#065f46'];
            const kgHTML = r.keluarga.map((k,i) => {
                const [ebg,ec] = {'Miskin':['#fee2e2','#991b1b'],'Menengah':['#fef3c7','#92400e']}[k.status_ekonomi] || ['#d1fae5','#065f46'];
                const gc = {'Laki-laki':['#dbeafe','#1d4ed8'],'Perempuan':['#fce7f3','#be185d']};
                return `<div style="padding:10px 0;border-top:${i>0?'1px solid #f1f5f9':'none'};">
                    <div style="display:flex;justify-content:space-between;align-items:start;gap:8px;margin-bottom:4px;">
                        <span style="font-weight:700;font-size:13px;color:#0f172a;">${esc(k.nama_kepala_keluarga)}</span>
                        <span style="font-size:10px;padding:1px 7px;border-radius:9999px;background:${ebg};color:${ec};font-weight:600;">${esc(k.status_ekonomi||'-')}</span>
                    </div>
                    <div style="font-size:11px;color:#64748b;margin-bottom:6px;">KK: ${esc(k.nomor_kk||'-')}</div>
                    <div style="font-size:11px;color:#64748b;">${k.penduduk.length} anggota:</div>
                    <div style="margin-top:4px;display:flex;flex-direction:column;gap:2px;">
                        ${k.penduduk.slice(0,5).map(p=>{const[gb,gc2]=gc[p.jenis_kelamin]||['#f1f5f9','#475569'];return`<div style="display:flex;align-items:center;gap:5px;font-size:11px;"><span style="width:16px;height:16px;border-radius:50%;background:${gb};display:inline-flex;align-items:center;justify-content:center;font-size:8px;color:${gc2};font-weight:700;">${p.jenis_kelamin==='Laki-laki'?'L':'P'}</span><span style="font-weight:500;color:#1e293b;">${esc(p.nama_lengkap)}</span></div>`;}).join('')}
                        ${k.penduduk.length>5?`<div style="font-size:10px;color:#94a3b8;margin-top:2px;">+${k.penduduk.length-5} lainnya</div>`:''}
                    </div>
                </div>`;
            }).join('');

            return `<div style="min-width:320px;max-width:360px;font-family:system-ui,-apple-system,sans-serif;background:#fff;">
                <div style="background:linear-gradient(135deg,#0f172a,#1e3a5f);padding:14px 16px 12px;position:relative;">
                    <div style="font-size:9px;color:#34d399;font-weight:700;letter-spacing:0.12em;margin-bottom:3px;">DATA RUMAH</div>
                    <div style="font-size:16px;font-weight:800;color:#fff;letter-spacing:-0.01em;">${esc(r.kode_rumah)}</div>
                    <div style="position:absolute;top:12px;right:40px;"><span style="font-size:10px;padding:2px 8px;border-radius:9999px;background:${bg};color:${fc};font-weight:700;">${esc(r.status_rumah||'-')}</span></div>
                </div>
                <div style="padding:12px 16px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <div style="text-align:center;"><div style="font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">${r.jumlah_kk}</div><div style="font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">KK</div></div>
                    <div style="text-align:center;"><div style="font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-0.02em;">${r.jumlah_penduduk}</div><div style="font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Orang</div></div>
                    <div style="text-align:center;"><div style="font-size:11px;font-weight:800;color:#0f172a;letter-spacing:-0.01em;margin-top:4px;">${esc(r.desa)}</div><div style="font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.04em;">Desa</div></div>
                </div>
                <div style="padding:10px 16px;font-size:12px;color:#475569;border-bottom:1px solid #e2e8f0;"><strong style="color:#0f172a;">Alamat:</strong> ${esc(r.alamat||'-')}</div>
                <div style="padding:4px 16px 12px;max-height:260px;overflow-y:auto;">
                    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;margin:10px 0 4px;">Keluarga</div>
                    ${kgHTML}
                </div>
            </div>`;
        };

        // ADD MARKERS
        const allMarkers = [];
        const bounds = L.latLngBounds();

        markersData.forEach((r) => {
            const pos = [r.latitude, r.longitude];
            const marker = L.marker(pos, { icon: icons[r.density] || icons.default })
                .bindPopup(buildPopup(r), { maxWidth: 380, closeButton: true })
                .bindTooltip(r.kode_rumah, { permanent: false, direction: 'top' });
            marker.rumahData = r;
            allMarkers.push(marker);
            mcg.addLayer(marker);
            bounds.extend(pos);
        });

        map.addLayer(mcg);
        const spatialFeatures = spatialData.features || [];
        let visibleSpatialFeatures = spatialFeatures;
        const spatialStyle = {
            style: feature => {
                const isDemo = Boolean(feature?.properties?.is_demo);
                const isRoute = feature?.geometry?.type === 'LineString';
                return {
                    color: isDemo ? '#7c3aed' : '#0284c7',
                    weight: isRoute ? (isDemo ? 3 : 4) : 2,
                    opacity: isDemo ? .78 : .92,
                    dashArray: isDemo && isRoute ? '7 7' : null,
                    lineCap: 'round',
                    lineJoin: 'round',
                    className: isRoute ? (isDemo ? 'pp-route-demo' : 'pp-route-published') : '',
                    fillColor: isDemo ? '#a78bfa' : '#38bdf8',
                    fillOpacity: isDemo ? .13 : .2,
                };
            },
            pointToLayer: (feature, latlng) => {
                const color = feature?.properties?.is_demo ? '#7c3aed' : '#0284c7';
                const icon = L.divIcon({
                    className: 'pp-feature-pin',
                    html: `<svg xmlns="http://www.w3.org/2000/svg" width="28" height="36" viewBox="0 0 28 36" aria-hidden="true"><path d="M14 1.5C7.1 1.5 2 6.6 2 13.2c0 8.2 12 21.2 12 21.2s12-13 12-21.2C26 6.6 20.9 1.5 14 1.5Z" fill="${color}" stroke="white" stroke-width="2.2"/><circle cx="14" cy="13" r="5.2" fill="white"/><circle cx="14" cy="13" r="2.2" fill="${color}"/></svg>`,
                    iconSize: [28, 36],
                    iconAnchor: [14, 35],
                    popupAnchor: [0, -32],
                });
                return L.marker(latlng, {icon, keyboard: true, riseOnHover: true});
            },
            onEachFeature: (feature, layer) => {
                const props = feature.properties || {};
                const attributes = Object.entries(props.attributes || {}).filter(([, value]) => value !== null && value !== '');
                const rows = attributes.length
                    ? attributes.map(([key, value]) => `<div class="pp-feature-row"><span>${esc(key)}</span><strong>${esc(value)}</strong></div>`).join('')
                    : '<div class="pp-feature-popup__empty">Belum ada atribut tambahan pada data ini.</div>';
                const demoNotice = props.is_demo ? '<div class="pp-feature-popup__notice">DATA DEMO · BUKAN DATA RESMI · BELUM DITERBITKAN</div>' : '';
                const popup = `<article class="pp-feature-popup"><header class="pp-feature-popup__header"><div class="pp-feature-popup__eyebrow">${esc(props.dataset || 'Data sektoral')}</div><strong class="pp-feature-popup__title">${esc(props.name || 'Lokasi')}</strong><div class="pp-feature-popup__meta">${esc(props.opd || 'OPD')} · ${esc(props.year || 'Tahun tidak dicantumkan')}${props.period ? ' · ' + esc(props.period) : ''}</div></header>${demoNotice}<div class="pp-feature-popup__rows">${rows}</div></article>`;
                const popupWidth = Math.max(250, Math.min(390, window.innerWidth - 56));
                layer.bindPopup(popup, {maxWidth:popupWidth,minWidth:Math.min(300,popupWidth),autoPanPadding:[24,32],keepInView:true,className:'pp-feature-popup-container'});
                layer.on('popupopen', () => layer.closeTooltip());
                const title = props.name || props.dataset;
                if (title) layer.bindTooltip(esc(title), {sticky: true});
            }
        };
        const spatialLayer = L.geoJSON([], spatialStyle).addTo(map);
        const opdFilter = document.getElementById('pp-filter-desa');
        const datasetFilter = document.getElementById('pp-filter-density');
        const statusFilter = document.getElementById('pp-filter-disease');
        [...new Set(spatialFeatures.map(feature => feature.properties?.opd).filter(Boolean))].sort().forEach(name => opdFilter.add(new Option(name, name)));
        [...new Set(spatialFeatures.map(feature => feature.properties?.dataset).filter(Boolean))].sort().forEach(name => datasetFilter.add(new Option(name, name)));
        const renderSpatialFeatures = (fit = false) => {
            const selectedOpd = opdFilter.value;
            const selectedDataset = datasetFilter.value;
            visibleSpatialFeatures = spatialFeatures.filter(feature =>
                (!selectedOpd || feature.properties?.opd === selectedOpd) &&
                (!selectedDataset || feature.properties?.dataset === selectedDataset) &&
                (statusFilter.value === 'all' || (statusFilter.value === 'demo') === Boolean(feature.properties?.is_demo))
            );
            spatialLayer.clearLayers();
            spatialLayer.addData(visibleSpatialFeatures);
            if (fit && spatialLayer.getBounds().isValid()) map.fitBounds(spatialLayer.getBounds(), {padding: [40, 40], maxZoom: 15});
            updateStats();
        };
        opdFilter.addEventListener('change', () => renderSpatialFeatures(true));
        datasetFilter.addEventListener('change', () => renderSpatialFeatures(true));
        statusFilter.addEventListener('change', () => renderSpatialFeatures(true));
        if (spatialLayer.getBounds().isValid()) bounds.extend(spatialLayer.getBounds());
        if (bounds.isValid()) map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });

        // District boundary and published-data choropleth from the converted SHP GeoJSON.
        const districtIds = new Map((thematicData.kecamatans || []).map(item => [String(item.name || '').toLocaleLowerCase('id-ID').replace(/[^\p{L}\p{N}]/gu, ''), String(item.id)]));
        const indicatorSelect = document.getElementById('pp-indicator');
        const thematicLegend = document.getElementById('pp-thematic-legend');
        const indicatorById = new Map((thematicData.indicators || []).map(indicator => [indicator.id, indicator]));
        const normalizeDistrictName = name => String(name || '').toLocaleLowerCase('id-ID').replace(/[^\p{L}\p{N}]/gu, '');
        const districtNameFor = feature => feature?.properties?.WADMKC || feature?.properties?.NAMOBJ || feature?.properties?.nama || feature?.properties?.name || '';
        const districtById = new Map((thematicData.kecamatans || []).map(item => [String(item.id), item]));
        (thematicData.indicators || []).forEach(indicator => {
            const option = new Option(`${indicator.opd} · ${indicator.dataset} · ${indicator.label} (${indicator.year})`, indicator.id);
            indicatorSelect.add(option);
        });
        let districtFeatures = [];
        const districtPopupOptions = () => {
            const width = Math.max(250, Math.min(420, window.innerWidth - 56));
            return {maxWidth:width,minWidth:Math.min(300,width),autoPanPadding:[24,32],keepInView:true,className:'pp-feature-popup-container'};
        };
        const buildDistrictPopup = (districtName, districtId, selectedIndicator = null, selectedValue = null) => {
            const profile = districtId ? (thematicData.profiles || {})[String(districtId)] || {} : {};
            const diseases = profile.diseases || [];
            const diseaseCases = diseases.reduce((sum, disease) => sum + Number(disease.cases || 0), 0);
            const diseaseDeaths = diseases.reduce((sum, disease) => sum + Number(disease.deaths || 0), 0);
            const source = profile.disease_source || {};
            const metrics = (profile.indicators || [])
                .filter(metric => metric.dataset_code !== 'SURVEILANS_PENYAKIT')
                .sort((a, b) => {
                    const rank = metric => /penduduk|sekolah|murid|puskesmas|stunting|jalan|jembatan|produksi|fasilitas/i.test(`${metric.label} ${metric.dataset}`) ? 0 : 1;
                    return rank(a) - rank(b) || a.dataset.localeCompare(b.dataset);
                }).slice(0, 6);
            const diseaseRows = diseases.slice(0, 6).map(disease => `<div class="pp-district-row"><div><div class="pp-district-row__name">${esc(disease.name)}${disease.code ? ` <span style="color:#64748b;font-weight:600">(${esc(disease.code)})</span>` : ''}</div><div class="pp-district-row__meta">${Number(disease.deaths || 0).toLocaleString('id-ID')} meninggal · ${Number(disease.hospitalized || 0).toLocaleString('id-ID')} dirawat</div></div><div class="pp-district-row__value">${Number(disease.cases || 0).toLocaleString('id-ID')} kasus</div></div>`).join('');
            const metricRows = metrics.map(metric => `<div class="pp-district-row"><div><div class="pp-district-row__name">${esc(metric.label)}</div><div class="pp-district-row__meta">${esc(metric.dataset)} · ${esc(metric.opd)} · ${esc(metric.year)}</div></div><div class="pp-district-row__value">${Number(metric.value).toLocaleString('id-ID', {maximumFractionDigits:2})}${metric.unit ? ' ' + esc(metric.unit) : ''}</div></div>`).join('');
            const selected = selectedIndicator
                ? `<section class="pp-district-section"><div class="pp-district-section__title">Indikator peta yang dipilih</div><div class="pp-district-empty"><strong>${esc(selectedIndicator.label)}:</strong> ${selectedValue === null || selectedValue === undefined ? 'belum ada data terbit di kecamatan ini' : `${Number(selectedValue).toLocaleString('id-ID', {maximumFractionDigits:2})}${selectedIndicator.unit ? ' ' + esc(selectedIndicator.unit) : ''}`}<br>${esc(selectedIndicator.dataset)} · ${esc(selectedIndicator.opd)} · ${esc(selectedIndicator.year)}</div></section>`
                : '';
            const diseaseContent = diseases.length
                ? `<div class="pp-district-list">${diseaseRows}</div><div class="pp-district-row__meta" style="padding:5px 2px">Sumber: ${esc(source.dataset || 'Surveilans Penyakit')} · ${esc(source.opd || 'Dinas Kesehatan')} · ${esc(source.year || '')} · data terbit</div>`
                : '<div class="pp-district-empty">Belum ada data penyakit terbit untuk kecamatan ini.</div>';
            const metricContent = metrics.length
                ? `<div class="pp-district-list">${metricRows}</div>`
                : '<div class="pp-district-empty">Belum ada indikator sektoral terbit lain untuk kecamatan ini.</div>';
            const districtCode = districtId ? districtById.get(String(districtId))?.code : null;
            return `<article class="pp-district-popup"><header class="pp-district-popup__header"><div class="pp-district-popup__eyebrow">Ringkasan wilayah</div><strong class="pp-district-popup__title">${esc(districtName || 'Kecamatan')}</strong><div class="pp-district-popup__meta">${districtCode ? `Kode BPS ${esc(districtCode)} · ` : ''}Kabupaten Kepulauan Sangihe · data terbit</div></header><div class="pp-district-popup__content"><div class="pp-district-kpis"><div class="pp-district-kpi"><strong>${diseases.length ? diseaseCases.toLocaleString('id-ID') : '—'}</strong><span>Total kasus penyakit${source.year ? ` · ${esc(source.year)}` : ''}</span></div><div class="pp-district-kpi"><strong>${diseases.length ? diseaseDeaths.toLocaleString('id-ID') : metrics.length.toLocaleString('id-ID')}</strong><span>${diseases.length ? 'Kematian tercatat' : 'Indikator sektoral tersedia'}</span></div></div>${selected}<section class="pp-district-section"><div class="pp-district-section__title">Penyakit terlapor</div>${diseaseContent}</section><section class="pp-district-section"><div class="pp-district-section__title">Informasi sektoral lain</div>${metricContent}</section></div></article>`;
        };
        let choroplethLayer = L.geoJSON([], {
            style: feature => feature.properties?.themeValue === null
                ? {color:'#64748b',weight:1.5,fillColor:'#cbd5e1',fillOpacity:.38}
                : {color:'#fff',weight:1.5,fillColor:feature.properties?.themeColor || '#60a5fa',fillOpacity:.76},
            onEachFeature: (feature, layer) => {
                const props = feature.properties || {};
                const indicator = props.themeIndicator;
                const districtName = props.districtName || districtNameFor(feature);
                const districtId = districtIds.get(normalizeDistrictName(districtName));
                layer.bindPopup(buildDistrictPopup(districtName, districtId, indicator, props.themeValue), districtPopupOptions());
                layer.on('click', event => L.DomEvent.stopPropagation(event));
                layer.on('popupopen', () => layer.closeTooltip());
                if (districtName) layer.bindTooltip(esc(districtName), {sticky:true});
            }
        }).addTo(map);
        const boundaryLayer = L.geoJSON([], {
            style: () => ({color:'#0f766e',weight:1.4,fillColor:'#ccfbf1',fillOpacity:.18}),
            onEachFeature: (feature, layer) => {
                const name = districtNameFor(feature);
                const districtId = districtIds.get(normalizeDistrictName(name));
                if (name) {
                    layer.bindPopup(buildDistrictPopup(name, districtId), districtPopupOptions());
                    layer.on('click', event => L.DomEvent.stopPropagation(event));
                    layer.on('popupopen', () => layer.closeTooltip());
                    layer.bindTooltip(String(name), {sticky:true});
                }
            }
        }).addTo(map);
        const indicatorColor = (value, min, max) => {
            if (max <= min) return '#2563eb';
            const ratio = (value - min) / (max - min);
            if (ratio <= .2) return '#dbeafe';
            if (ratio <= .4) return '#93c5fd';
            if (ratio <= .6) return '#60a5fa';
            if (ratio <= .8) return '#2563eb';
            return '#1e3a8a';
        };
        const numberLabel = value => Number(value).toLocaleString('id-ID', {maximumFractionDigits:2});
        const renderChoropleth = () => {
            const indicator = indicatorById.get(indicatorSelect.value);
            if (!indicator || districtFeatures.length === 0) {
                choroplethLayer.clearLayers();
                thematicLegend.classList.remove('is-visible');
                return;
            }
            const numbers = Object.values(indicator.values || {}).map(Number).filter(Number.isFinite);
            const min = numbers.length ? Math.min(...numbers) : 0;
            const max = numbers.length ? Math.max(...numbers) : 0;
            const features = districtFeatures.map(feature => {
                const districtName = districtNameFor(feature);
                const districtId = districtIds.get(String(districtName).toLocaleLowerCase('id-ID').replace(/[^\p{L}\p{N}]/gu, ''));
                const value = districtId ? indicator.values?.[districtId] : undefined;
                const hasValue = value !== undefined && value !== null && Number.isFinite(Number(value));
                return {...feature, properties:{...feature.properties, districtName, themeIndicator:indicator, themeValue:hasValue ? Number(value) : null, themePeriod:districtId ? indicator.periods?.[districtId] : null, themeColor:!hasValue ? null : indicatorColor(Number(value),min,max)}};
            });
            choroplethLayer.clearLayers();
            choroplethLayer.addData(features);
            choroplethLayer.bringToFront();
            thematicLegend.classList.add('is-visible');
            document.getElementById('pp-legend-title').textContent = `${indicator.label} · ${indicator.year}`;
            document.getElementById('pp-legend-description').textContent = `${indicator.dataset} — ${indicator.opd}; nilai ${indicator.aggregation === 'average' ? 'dirata-ratakan' : 'dijumlahkan'} dari data terbit tahun ${indicator.year}.`;
            document.getElementById('pp-legend-min').textContent = `${numberLabel(min)}${indicator.unit ? ' ' + indicator.unit : ''}`;
            document.getElementById('pp-legend-max').textContent = `${numberLabel(max)}${indicator.unit ? ' ' + indicator.unit : ''}`;
        };
        indicatorSelect.addEventListener('change', renderChoropleth);
        fetch('{{ asset('data/batas_wilayah.geojson') }}')
            .then(response => response.ok ? response.json() : null)
            .then(geojson => {
                if (!geojson) return;
                districtFeatures = (geojson.features || []).filter(feature => districtNameFor(feature));
                boundaryLayer.addData(districtFeatures);
                renderChoropleth();
                const districtBounds = boundaryLayer.getBounds();
                if (districtBounds.isValid()) {
                    bounds.extend(districtBounds);
                    if (!spatialLayer.getBounds().isValid() && markersData.length === 0) map.fitBounds(districtBounds, {padding:[36,36],maxZoom:11});
                }
            }).catch(() => {});

        // MAP CONTROLS
        document.getElementById('pp-map-mode').addEventListener('change',e=>{
            map.removeLayer(currentLayer);
            currentLayer=e.target.value==='satellite'?satellite:e.target.value==='topo'?topo:osm;
            map.addLayer(currentLayer);
        });
        document.getElementById('pp-zoom-in').onclick=()=>map.zoomIn();
        document.getElementById('pp-zoom-out').onclick=()=>map.zoomOut();
        document.getElementById('pp-fit-bounds').onclick=()=>{const visibleBounds=spatialLayer.getBounds();if(visibleBounds.isValid())map.fitBounds(visibleBounds,{padding:[50,50],maxZoom:17});else if(bounds.isValid())map.fitBounds(bounds,{padding:[50,50],maxZoom:17});};
        document.getElementById('pp-locate').onclick=()=>{if(navigator.geolocation)navigator.geolocation.getCurrentPosition(p=>map.flyTo([p.coords.latitude,p.coords.longitude],16),()=>{});};

        // FILTERS
        const fDesa=document.getElementById('pp-filter-desa');
        const fDens=document.getElementById('pp-filter-density');

        const applyF=()=>{
            const sd=fDesa.value,ss=fDens.value;
            allMarkers.forEach(m=>{
                const d=m.rumahData;
                const ok=(!sd||d.desa===sd)&&(!ss||d.density===ss);
                ok?mcg.addLayer(m):mcg.removeLayer(m);
            });
            updateStats();
        };

        fDesa.addEventListener('change',()=>{applyF();renderSpatialFeatures(true);});
        fDens.addEventListener('change',()=>{applyF();renderSpatialFeatures(true);});

        // STATS
        const updateStats=()=>{const datasets=new Set(visibleSpatialFeatures.map(f=>f.properties?.dataset).filter(Boolean));const opds=new Set(visibleSpatialFeatures.map(f=>f.properties?.opd).filter(Boolean));document.getElementById('pp-total-rumah').textContent=visibleSpatialFeatures.length.toLocaleString('id-ID');document.getElementById('pp-total-kk').textContent=datasets.size.toLocaleString('id-ID');document.getElementById('pp-total-penduduk').textContent=opds.size.toLocaleString('id-ID');};

        // SEARCH
        const sIn=document.getElementById('pp-search');
        const sRes=document.getElementById('pp-search-results');
        sIn.addEventListener('input',e=>{
            const q=e.target.value.toLowerCase().trim();
            if(q.length<2){sRes.classList.remove('is-open');return;}
            const f=spatialFeatures.filter(feature=>{
                const p=feature.properties||{};
                return [p.name,p.dataset,p.opd,...Object.keys(p.attributes||{}),...Object.values(p.attributes||{})].some(value=>String(value||'').toLowerCase().includes(q));
            }).slice(0,10);
            if(f.length===0){sRes.innerHTML=`<div class="pp-search-empty">Tidak ada hasil ditemukan</div>`;sRes.classList.add('is-open');return;}
            sRes.innerHTML=f.map((feature,i)=>`<div class="pp-search-item" data-idx="${i}"><div class="pp-search-item-name">${esc(feature.properties?.name||feature.properties?.dataset||'Lokasi')}</div><div class="pp-search-item-meta">${esc(feature.properties?.opd||'OPD')} &bull; ${esc(feature.properties?.dataset||'Data sektoral')}${feature.properties?.is_demo?' · DEMO':''}</div></div>`).join('');
            sRes.classList.add('is-open');
            sRes.querySelectorAll('.pp-search-item').forEach(it=>{it.onclick=()=>{const feature=f[parseInt(it.dataset.idx)];opdFilter.value=feature.properties?.opd||'';datasetFilter.value=feature.properties?.dataset||'';statusFilter.value=feature.properties?.is_demo?'demo':'published';renderSpatialFeatures(false);const target=L.geoJSON(feature);if(target.getBounds().isValid())map.flyToBounds(target.getBounds(),{padding:[50,50],maxZoom:16,duration:.8});sIn.value='';sRes.classList.remove('is-open');};});
        });
        document.addEventListener('click',e=>{if(!e.target.closest('#pp-search')&&!e.target.closest('.pp-search-results'))sRes.classList.remove('is-open');});

        // CLEAR
        document.getElementById('pp-clear').onclick=()=>{sIn.value='';fDesa.value='';fDens.value='';statusFilter.value='all';indicatorSelect.value='';renderChoropleth();renderSpatialFeatures(false);document.getElementById('pp-map-mode').value='osm';if(currentLayer!==osm){map.removeLayer(currentLayer);currentLayer=osm;map.addLayer(osm);}applyF();const visibleBounds=boundaryLayer.getBounds();if(visibleBounds.isValid())map.fitBounds(visibleBounds,{padding:[50,50],maxZoom:11});};

        // LAYER CONTROL
        L.control.layers({'Peta jalan':osm,'Satelit':satellite,'Topografi':topo},{'Batas kecamatan':boundaryLayer,'Sebaran per kecamatan':choroplethLayer,'Lokasi data sektoral':spatialLayer}).addTo(map);

        // INIT
        statusFilter.value='all';
        renderSpatialFeatures(true);
        updateStats();

        // LEAFLET STYLES
        const st=document.createElement('style');
        st.textContent='.pp-mi,.pp-cl{background:transparent!important;border:none!important}.leaflet-popup-content-wrapper{border-radius:14px!important;box-shadow:0 20px 40px rgba(0,0,0,0.15)!important;border:1px solid rgba(226,232,240,0.8)!important;overflow:hidden!important;padding:0!important}.leaflet-popup-content{margin:0!important;width:auto!important}.leaflet-popup-close-button{top:10px!important;right:10px!important;width:28px!important;height:28px!important;padding:0!important;font-size:20px!important;line-height:28px!important;text-align:center!important;border-radius:50%!important;background:rgba(241,245,249,0.9)!important;color:#64748b!important;border:none!important;font-weight:300!important}.leaflet-popup-close-button:hover{background:#ef4444!important;color:white!important}.leaflet-popup-tip-container{display:none!important}.leaflet-tooltip{background:#0f172a!important;border:none!important;border-radius:8px!important;color:#f1f5f9!important;font-size:12px!important;font-weight:700!important;padding:6px 10px!important;box-shadow:0 4px 12px rgba(0,0,0,0.2)!important;white-space:nowrap!important}.leaflet-tooltip::before{display:none!important}.leaflet-control-layers{border-radius:10px!important;border:1px solid #e2e8f0!important;box-shadow:0 4px 12px rgba(0,0,0,0.1)!important;overflow:hidden!important}.leaflet-control-attribution{font-size:10px!important;background:rgba(255,255,255,0.8)!important;padding:2px 6px!important}';
        document.head.appendChild(st);
    });
    </script>
</x-filament-panels::page>
