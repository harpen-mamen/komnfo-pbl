{{-- SIPANDU Dashboard - Bersih, Tegas, Elegan
     Memakai data yang sudah dihitung oleh Dashboard::getViewData() --}}
<x-filament-panels::page>
@php
    $viewData = $this->getViewData() ?? [];
    $dashboard = $viewData['dashboard'] ?? [];
    $mode = $dashboard['mode'] ?? 'default';
    $header = $dashboard['header'] ?? [];
    $kpis = $dashboard['kpis'] ?? [];
    $user = auth()->user();
    $greetingName = $user?->name ?? 'Pengguna';
    $firstName = explode(' ', $greetingName)[0];
    $greetingHour = (int) now()->format('H');
    $greeting = match(true) {
        $greetingHour < 11 => 'Selamat Pagi',
        $greetingHour < 15 => 'Selamat Siang',
        $greetingHour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };

    // Tone mapping untuk KPI icon-box
    $toneIcon = [
        'primary' => 'o-cube',
        'success' => 'o-check-circle',
        'warning' => 'o-exclamation-triangle',
        'danger' => 'o-x-circle',
        'info' => 'o-information-circle',
        'gray' => 'o-cube',
    ];
@endphp

<div class="ds-dashboard">
    {{-- HERO --}}
    <header class="ds-hero">
        <div class="ds-hero__bg"></div>
        <div class="ds-hero__content">
            <span class="ds-badge ds-badge--primary ds-badge--dot">{{ strtoupper($mode) }}</span>
            <h1 class="ds-hero__title">{{ $greeting }}, {{ $firstName }}.</h1>
            <p class="ds-hero__subtitle">
                {{ $header['summary'] ?? ($header['subtitle'] ?? 'Pangkalan Data Terpadu Kabupaten Kepulauan Sangihe.') }}
            </p>
            <div class="ds-hero__meta">
                <div class="ds-hero__meta-item">
                    <span class="ds-eyebrow ds-hero__meta-label">SCOPE</span>
                    <span class="ds-hero__meta-value">{{ $header['scope'] ?? 'SIPANDU' }}</span>
                </div>
                <div class="ds-hero__meta-item">
                    <span class="ds-eyebrow ds-hero__meta-label">TANGGAL</span>
                    <span class="ds-hero__meta-value">{{ $header['date'] ?? now()->translatedFormat('d F Y') }}</span>
                </div>
                <div class="ds-hero__meta-item">
                    <span class="ds-eyebrow ds-hero__meta-label">WAKTU</span>
                    <span class="ds-hero__meta-value">{{ $header['time'] ?? now()->format('H:i') }} WIB</span>
                </div>
                @if(!empty($header['login_status']))
                <div class="ds-hero__meta-item">
                    <span class="ds-eyebrow ds-hero__meta-label">STATUS</span>
                    <span class="ds-hero__meta-value">
                        <span class="ds-badge ds-badge--success ds-badge--dot">{{ $header['login_status'] }}</span>
                    </span>
                </div>
                @endif
            </div>
        </div>
        <div class="ds-hero__decor">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <defs>
                    <linearGradient id="heroGrad" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#6366f1" stop-opacity="0.5"/>
                        <stop offset="100%" stop-color="#0ea5e9" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <circle cx="100" cy="100" r="90" fill="url(#heroGrad)"/>
            </svg>
        </div>
    </header>

    {{-- KPI GRID --}}
    @if(!empty($kpis))
    <section class="ds-kpi-grid">
        @foreach($kpis as $kpi)
            @php
                $tone = $kpi['color'] ?? 'primary';
                $iconName = $toneIcon[$tone] ?? 'o-cube';
            @endphp
            <article class="ds-kpi">
                <div class="ds-kpi__icon ds-kpi__icon--{{ $tone }}">
                    <x-dynamic-component :component="'heroicon-' . $iconName" class="w-5 h-5" />
                </div>
                <p class="ds-kpi__label">{{ $kpi['label'] ?? '' }}</p>
                <p class="ds-kpi__value">{{ $kpi['value'] ?? 0 }}</p>
                @if(!empty($kpi['description']))
                    <p class="ds-kpi__delta">{{ $kpi['description'] }}</p>
                @endif
            </article>
        @endforeach
    </section>
    @endif

    {{-- Quick Actions --}}
    @if(!empty($dashboard['quickActions']))
    <section class="ds-card">
        <header class="ds-card-header">
            <h2 class="ds-heading ds-heading--md">Aksi Cepat</h2>
            <p class="ds-text ds-text--muted ds-text--sm ds-mt-2">Pintasan ke fitur yang sering digunakan</p>
        </header>
        <div class="ds-card-body">
            <div class="ds-action-grid">
                @foreach($dashboard['quickActions'] as $action)
                    <a href="{{ $action['url'] ?? '#' }}" class="ds-action ds-action--{{ $action['color'] ?? 'primary' }}">
                        <div class="ds-action__icon">
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </div>
                        <div class="ds-action__body">
                            <p class="ds-action__label">{{ $action['label'] ?? 'Aksi' }}</p>
                            <p class="ds-action__desc">{{ $action['description'] ?? '' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Task Center (Kecamatan/Operator mode) --}}
    @if(!empty($dashboard['taskCenter']['items']) && $mode !== 'kabupaten')
    <section class="ds-card">
        <header class="ds-card-header">
            <h2 class="ds-heading ds-heading--md">{{ $dashboard['taskCenter']['heading'] ?? 'Task Center' }}</h2>
            <p class="ds-text ds-text--muted ds-text--sm ds-mt-2">{{ $dashboard['taskCenter']['description'] ?? '' }}</p>
        </header>
        <div class="ds-card-body">
            <ul class="ds-task-list">
                @foreach($dashboard['taskCenter']['items'] as $task)
                    @if(!empty($task['available']))
                    <li class="ds-task">
                        <div class="ds-task__count ds-task__count--{{ $task['color'] ?? 'primary' }}">
                            {{ $task['count'] ?? 0 }}
                        </div>
                        <div class="ds-task__body">
                            <p class="ds-task__label">{{ $task['label'] ?? '' }}</p>
                            <p class="ds-task__desc">{{ $task['description'] ?? '' }}</p>
                        </div>
                        <span class="ds-badge ds-badge--{{ $task['color'] ?? 'primary' }}">{{ $task['status'] ?? '' }}</span>
                    </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    {{-- Progress per Desa (Kecamatan mode) --}}
    @if(!empty($dashboard['villageProgress']) && $mode === 'kecamatan')
    <section class="ds-card">
        <header class="ds-card-header">
            <h2 class="ds-heading ds-heading--md">Progress Per Desa</h2>
        </header>
        <div class="ds-card-body">
            <div class="ds-table-wrap">
                <table class="ds-table">
                    <thead>
                        <tr>
                            <th>Desa</th>
                            <th class="ds-text--right">Rumah</th>
                            <th class="ds-text--right">KK</th>
                            <th class="ds-text--right">Penduduk</th>
                            <th class="ds-text--right">Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboard['villageProgress'] as $village)
                        <tr>
                            <td class="ds-text--strong">{{ $village['nama'] ?? '-' }}</td>
                            <td class="ds-text--right">{{ number_format($village['rumah'] ?? 0) }}</td>
                            <td class="ds-text--right">{{ number_format($village['keluarga'] ?? 0) }}</td>
                            <td class="ds-text--right">{{ number_format($village['penduduk'] ?? 0) }}</td>
                            <td>
                                <div class="ds-progress">
                                    <div class="ds-progress__bar" style="width: {{ $village['progress'] ?? 0 }}%"></div>
                                </div>
                                <span class="ds-text--sm ds-text--muted ds-mt-2">{{ $village['progress'] ?? 0 }}%</span>
                            </td>
                            <td>
                                <span class="ds-badge ds-badge--{{ $village['color'] ?? 'gray' }}">{{ $village['status'] ?? '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @endif

    {{-- Monitoring Kecamatan (Kabupaten mode) --}}
    @if(!empty($dashboard['monitoring']['rows']))
    <section class="ds-card">
        <header class="ds-card-header">
            <h2 class="ds-heading ds-heading--md">{{ $dashboard['monitoring']['heading'] ?? 'Monitoring Kecamatan' }}</h2>
        </header>
        <div class="ds-card-body">
            <div class="ds-table-wrap">
                <table class="ds-table">
                    <thead>
                        <tr>
                            <th>Kecamatan</th>
                            <th class="ds-text--right">Rumah</th>
                            <th class="ds-text--right">KK</th>
                            <th class="ds-text--right">Penduduk</th>
                            <th class="ds-text--right">Progress</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dashboard['monitoring']['rows'] as $row)
                        <tr>
                            <td class="ds-text--strong">{{ $row['nama'] ?? '-' }}</td>
                            <td class="ds-text--right">{{ number_format($row['rumah'] ?? 0) }}</td>
                            <td class="ds-text--right">{{ number_format($row['keluarga'] ?? 0) }}</td>
                            <td class="ds-text--right">{{ number_format($row['penduduk'] ?? 0) }}</td>
                            <td>
                                <div class="ds-progress">
                                    <div class="ds-progress__bar" style="width: {{ $row['progress'] ?? 0 }}%"></div>
                                </div>
                                <span class="ds-text--sm ds-text--muted ds-mt-2">{{ $row['progress'] ?? 0 }}%</span>
                            </td>
                            <td>
                                <span class="ds-badge ds-badge--{{ $row['color'] ?? 'gray' }}">{{ $row['status'] ?? '-' }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @endif

    {{-- Status summary cards (Kabupaten mode) --}}
    @if(!empty($dashboard['verificationSummary']))
    <section class="ds-status-grid">
        @foreach($dashboard['verificationSummary'] as $status)
        <article class="ds-status-card">
            <p class="ds-status-card__label">{{ $status['label'] ?? '-' }}</p>
            <p class="ds-status-card__value">{{ number_format($status['count'] ?? 0) }}</p>
            <div class="ds-status-card__bar ds-status-card__bar--{{ $status['color'] ?? 'gray' }}"></div>
        </article>
        @endforeach
    </section>
    @endif

    {{-- Activity Timeline --}}
    @if(!empty($dashboard['activityTimeline']) && $mode === 'kabupaten')
    <section class="ds-card">
        <header class="ds-card-header">
            <h2 class="ds-heading ds-heading--md">Aktivitas Terbaru</h2>
        </header>
        <div class="ds-card-body">
            <ul class="ds-activity">
                @foreach(array_slice($dashboard['activityTimeline'], 0, 5) as $activity)
                <li class="ds-activity__item">
                    <div class="ds-activity__dot"></div>
                    <div class="ds-activity__body">
                        <p class="ds-activity__title">{{ $activity['title'] ?? $activity['deskripsi'] ?? '-' }}</p>
                        <p class="ds-activity__time">{{ $activity['time'] ?? ($activity['created_at'] ?? '') }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif
</div>
</x-filament-panels::page>

<style>
.ds-dashboard {
    display: flex;
    flex-direction: column;
    gap: var(--ds-space-6);
    max-width: 1400px;
    margin: 0 auto;
}

/* HERO */
.ds-hero {
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #312e81 100%);
    border-radius: var(--ds-radius-lg);
    padding: var(--ds-space-8) var(--ds-space-8);
    color: white;
    overflow: hidden;
    box-shadow: var(--ds-shadow-lg);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--ds-space-6);
}

.ds-hero__bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 50%, rgba(99,102,241,0.20) 0%, transparent 50%),
        radial-gradient(circle at 80% 100%, rgba(14,165,233,0.15) 0%, transparent 50%);
    pointer-events: none;
}

.ds-hero__content { position: relative; z-index: 2; flex: 1; }
.ds-hero__title {
    font-family: var(--ds-font-display);
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    color: white;
    margin: var(--ds-space-3) 0;
}
.ds-hero__subtitle {
    color: rgba(255,255,255,0.7);
    font-size: 0.95rem;
    line-height: 1.6;
    max-width: 640px;
    margin: 0 0 var(--ds-space-6);
}
.ds-hero__meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--ds-space-6);
    align-items: flex-end;
}
.ds-hero__meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.ds-hero__meta-label {
    color: rgba(255,255,255,0.5);
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.ds-hero__meta-value {
    color: white;
    font-size: 0.9rem;
    font-weight: 700;
}
.ds-hero__decor {
    position: relative;
    width: 180px;
    height: 180px;
    flex-shrink: 0;
}
.ds-hero__decor svg { width: 100%; height: 100%; opacity: 0.6; }
@media (max-width: 768px) {
    .ds-hero { padding: var(--ds-space-6); flex-direction: column; align-items: flex-start; }
    .ds-hero__decor { display: none; }
    .ds-hero__title { font-size: 1.5rem; }
}

/* KPI GRID */
.ds-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--ds-space-4);
}

.ds-kpi {
    background: var(--ds-ink-0);
    border: 1px solid var(--ds-ink-200);
    border-radius: var(--ds-radius);
    padding: var(--ds-space-6);
    transition: all var(--ds-transition);
    position: relative;
    overflow: hidden;
}
.ds-kpi:hover {
    border-color: var(--ds-ink-300);
    box-shadow: var(--ds-shadow-sm);
    transform: translateY(-1px);
}
.ds-kpi__icon {
    width: 40px;
    height: 40px;
    border-radius: var(--ds-radius-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--ds-space-4);
}
.ds-kpi__icon--primary { background: var(--ds-primary-50); color: var(--ds-primary-600); }
.ds-kpi__icon--success { background: var(--ds-success-50); color: var(--ds-success-600); }
.ds-kpi__icon--warning { background: var(--ds-warning-50); color: var(--ds-warning-600); }
.ds-kpi__icon--danger  { background: var(--ds-danger-50);  color: var(--ds-danger-600); }
.ds-kpi__icon--info    { background: var(--ds-info-50);    color: var(--ds-info-600); }
.ds-kpi__icon--gray    { background: var(--ds-ink-100);    color: var(--ds-ink-600); }
.ds-kpi__label {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--ds-ink-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--ds-space-2);
}
.ds-kpi__value {
    font-family: var(--ds-font-display);
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--ds-ink-900);
    letter-spacing: -0.03em;
    line-height: 1;
    word-break: break-word;
}
.ds-kpi__delta {
    font-size: 0.75rem;
    color: var(--ds-ink-500);
    margin-top: var(--ds-space-2);
    line-height: 1.5;
}

/* ACTION GRID */
.ds-action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: var(--ds-space-3);
}
.ds-action {
    display: flex;
    align-items: flex-start;
    gap: var(--ds-space-3);
    padding: var(--ds-space-4);
    border: 1px solid var(--ds-ink-200);
    border-radius: var(--ds-radius);
    background: var(--ds-ink-0);
    text-decoration: none;
    color: var(--ds-ink-900);
    transition: all var(--ds-transition);
}
.ds-action:hover {
    border-color: var(--ds-primary-300);
    box-shadow: var(--ds-shadow-sm);
    transform: translateY(-1px);
}
.ds-action__icon {
    width: 32px;
    height: 32px;
    border-radius: var(--ds-radius-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--ds-primary-50);
    color: var(--ds-primary-600);
    flex-shrink: 0;
}
.ds-action--success .ds-action__icon { background: var(--ds-success-50); color: var(--ds-success-600); }
.ds-action--warning .ds-action__icon { background: var(--ds-warning-50); color: var(--ds-warning-600); }
.ds-action--gray    .ds-action__icon { background: var(--ds-ink-100);    color: var(--ds-ink-600); }
.ds-action__label {
    font-weight: 700;
    font-size: 0.875rem;
    color: var(--ds-ink-900);
    margin: 0;
}
.ds-action__desc {
    font-size: 0.75rem;
    color: var(--ds-ink-500);
    margin: 0.25rem 0 0;
    line-height: 1.5;
}

/* TASK LIST */
.ds-task-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--ds-space-3); }
.ds-task {
    display: flex;
    align-items: center;
    gap: var(--ds-space-4);
    padding: var(--ds-space-4);
    border: 1px solid var(--ds-ink-200);
    border-radius: var(--ds-radius);
    background: var(--ds-ink-50);
}
.ds-task__count {
    width: 56px;
    height: 56px;
    border-radius: var(--ds-radius);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-family: var(--ds-font-display);
    font-size: 1.5rem;
    font-weight: 800;
    background: var(--ds-primary-100);
    color: var(--ds-primary-700);
    flex-shrink: 0;
}
.ds-task__count--success { background: var(--ds-success-100); color: var(--ds-success-700); }
.ds-task__count--warning { background: var(--ds-warning-100); color: var(--ds-warning-700); }
.ds-task__count--danger  { background: var(--ds-danger-100);  color: var(--ds-danger-700); }
.ds-task__count--info    { background: var(--ds-info-100);    color: var(--ds-info-700); }
.ds-task__count--gray    { background: var(--ds-ink-200);     color: var(--ds-ink-700); }
.ds-task__body { flex: 1; }
.ds-task__label { font-weight: 700; color: var(--ds-ink-900); margin: 0; font-size: 0.875rem; }
.ds-task__desc  { font-size: 0.75rem; color: var(--ds-ink-500); margin: 0.25rem 0 0; }

/* TABLE */
.ds-table-wrap { overflow-x: auto; }
.ds-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.ds-table thead th {
    text-align: left;
    padding: var(--ds-space-3) var(--ds-space-4);
    background: var(--ds-ink-50);
    color: var(--ds-ink-500);
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid var(--ds-ink-200);
}
.ds-table tbody td {
    padding: var(--ds-space-4);
    border-bottom: 1px solid var(--ds-ink-100);
    color: var(--ds-ink-700);
}
.ds-table tbody tr:last-child td { border-bottom: 0; }
.ds-table tbody tr:hover td { background: var(--ds-ink-50); }
.ds-text--right { text-align: right; }
.ds-text--strong { color: var(--ds-ink-900); font-weight: 600; }
.ds-text--sm { font-size: 0.75rem; }

/* STATUS GRID */
.ds-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--ds-space-4);
}
.ds-status-card {
    background: var(--ds-ink-0);
    border: 1px solid var(--ds-ink-200);
    border-radius: var(--ds-radius);
    padding: var(--ds-space-5);
    position: relative;
    overflow: hidden;
}
.ds-status-card__label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--ds-ink-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 var(--ds-space-2);
}
.ds-status-card__value {
    font-family: var(--ds-font-display);
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--ds-ink-900);
    letter-spacing: -0.02em;
    line-height: 1;
    margin: 0;
}
.ds-status-card__bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--ds-primary-500);
}
.ds-status-card__bar--success { background: var(--ds-success-500); }
.ds-status-card__bar--warning { background: var(--ds-warning-500); }
.ds-status-card__bar--danger  { background: var(--ds-danger-500); }
.ds-status-card__bar--info    { background: var(--ds-info-500); }
.ds-status-card__bar--gray    { background: var(--ds-ink-400); }

/* ACTIVITY */
.ds-activity { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--ds-space-4); }
.ds-activity__item {
    display: flex;
    align-items: flex-start;
    gap: var(--ds-space-3);
}
.ds-activity__dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background: var(--ds-primary-500);
    margin-top: 0.5rem;
    flex-shrink: 0;
}
.ds-activity__title { font-size: 0.875rem; color: var(--ds-ink-900); font-weight: 500; margin: 0; }
.ds-activity__time { font-size: 0.75rem; color: var(--ds-ink-500); margin: 0.25rem 0 0; }

/* SECTION HEADERS */
.ds-section-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--ds-ink-900);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>
