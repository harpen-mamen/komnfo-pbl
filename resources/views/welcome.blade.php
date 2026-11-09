{{-- SIPANDU Public Landing - Bersih, Tegas, Elegan --}}
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pangkalan Data Terpadu - Satu Data Indonesia untuk Kabupaten Kepulauan Sangihe">
    <title>SIPANDU - Pangkalan Data Terpadu | Kabupaten Kepulauan Sangihe</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ is_file(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : '1' }}">
</head>
<body class="ds-landing">
    {{-- TOPBAR --}}
    <header class="ds-landing__topbar">
        <div class="ds-container ds-row ds-row--between" style="min-height: 72px;">
            <a href="/" class="ds-landing__logo">
                <div class="ds-landing__logo-mark">
                    <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <rect width="32" height="32" rx="7" fill="currentColor"/>
                        <path d="M9 16 L14 21 L23 12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                <div>
                    <p class="ds-landing__logo-text">SIPANDU</p>
                    <p class="ds-landing__logo-sub">Pangkalan Data Terpadu</p>
                </div>
            </a>
            <nav class="ds-landing__nav">
                <a href="#fitur" class="ds-landing__nav-link">Fitur</a>
                <a href="#domain" class="ds-landing__nav-link">Domain</a>
                <a href="#arsitektur" class="ds-landing__nav-link">Arsitektur</a>
                <a href="#kontak" class="ds-landing__nav-link">Kontak</a>
                <a href="{{ url('/admin') }}" class="ds-btn ds-btn--primary ds-btn--sm">
                    Masuk Panel
                </a>
            </nav>
        </div>
    </header>

    {{-- HERO --}}
    <section class="ds-hero ds-hero--landing">
        <div class="ds-hero__bg ds-hero__bg--landing"></div>
        <div class="ds-container ds-hero__content ds-hero__content--landing">
            <span class="ds-badge ds-badge--primary ds-badge--dot">SISTEM AKTIF · 2026</span>
            <h1 class="ds-hero__title ds-hero__title--landing">
                Pangkalan Data Terpadu
                <span class="ds-hero__title-accent">Kabupaten Kepulauan Sangihe</span>
            </h1>
            <p class="ds-hero__subtitle ds-hero__subtitle--landing">
                Integrasi master data, data sektoral 32 OPD, dan data lapangan desa dalam
                satu platform terpadu. Bersih, terstruktur, terverifikasi.
            </p>
            <div class="ds-hero__actions">
                <a href="{{ url('/admin') }}" class="ds-btn ds-btn--primary ds-btn--lg">
                    Buka Panel Admin
                    <x-heroicon-o-arrow-right class="w-4 h-4" />
                </a>
                <a href="#fitur" class="ds-btn ds-btn--ghost ds-btn--inverted ds-btn--lg">
                    Pelajari Sistem
                </a>
            </div>

            {{-- Live stats --}}
            <dl class="ds-hero__stats">
                <div class="ds-hero__stat">
                    <dt>32</dt>
                    <dd>OPD Terintegrasi</dd>
                </div>
                <div class="ds-hero__stat">
                    <dt>15+</dt>
                    <dd>Modul Data</dd>
                </div>
                <div class="ds-hero__stat">
                    <dt>4</dt>
                    <dd>Domain Data</dd>
                </div>
                <div class="ds-hero__stat">
                    <dt>100%</dt>
                    <dd>Traceable</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- FITUR --}}
    <section id="fitur" class="ds-section">
        <div class="ds-container">
            <header class="ds-section-header">
                <span class="ds-eyebrow">FITUR UTAMA</span>
                <h2 class="ds-heading ds-heading--xl">Dirancang untuk integrasi data yang bersih.</h2>
                <p class="ds-text ds-text--muted ds-mt-4" style="max-width: 640px;">
                    Setiap fitur mengikuti prinsip Satu Data Indonesia: standar, interoperable, dan dapat diaudit.
                </p>
            </header>

            <div class="ds-grid ds-grid--cards ds-mt-8">
                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box">
                            <x-heroicon-o-cube class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Master Data Tunggal</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Penduduk dan KK dikelola Disdukcapil sebagai sumber utama.
                            Tidak ada duplikasi input dari level lain.
                        </p>
                    </div>
                </article>

                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box" style="background: var(--ds-success-50); color: var(--ds-success-600);">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Dataset Generik</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Satu platform untuk 32 OPD. Dataset dikonfigurasi per-OPD
                            dengan field dinamis, periode fleksibel.
                        </p>
                    </div>
                </article>

                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box" style="background: var(--ds-warning-50); color: var(--ds-warning-600);">
                            <x-heroicon-o-map-pin class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Data Lapangan Spasial</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Rumah, sekolah, kantor, jalan  semua terhubung ke koordinat
                            dengan dukungan POINT, LINESTRING, dan POLYGON.
                        </p>
                    </div>
                </article>

                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box" style="background: var(--ds-info-50); color: var(--ds-info-600);">
                            <x-heroicon-o-device-phone-mobile class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Mobile Offline-First</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Admin Desa input data langsung dari lapangan. Bekerja tanpa internet,
                            sync otomatis ketika online.
                        </p>
                    </div>
                </article>

                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box" style="background: var(--ds-danger-50); color: var(--ds-danger-600);">
                            <x-heroicon-o-finger-print class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Source Attribution</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Setiap data terlacak: siapa input, kapan, dari mana.
                            Polymorphic source tracking ke seluruh entity.
                        </p>
                    </div>
                </article>

                <article class="ds-card ds-card--hover">
                    <div class="ds-card-body">
                        <div class="ds-icon-box" style="background: var(--ds-primary-50); color: var(--ds-primary-600);">
                            <x-heroicon-o-shield-check class="w-5 h-5" />
                        </div>
                        <h3 class="ds-heading ds-heading--md ds-mt-4">Workflow Per-Source</h3>
                        <p class="ds-text ds-text--muted ds-mt-2">
                            Master data, data lapangan, dan data sektoral memiliki
                            workflow verifikasi yang sesuai karakternya masing-masing.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- DOMAIN --}}
    <section id="domain" class="ds-section ds-section--alt">
        <div class="ds-container">
            <header class="ds-section-header">
                <span class="ds-eyebrow">4 DOMAIN DATA</span>
                <h2 class="ds-heading ds-heading--xl">Satu platform, empat domain terintegrasi.</h2>
            </header>

            <div class="ds-domain-grid ds-mt-8">
                <article class="ds-domain">
                    <div class="ds-domain__num">01</div>
                    <h3 class="ds-heading ds-heading--md">Master Data</h3>
                    <p class="ds-text ds-text--muted ds-mt-2">Penduduk, Keluarga, Wilayah. Bersumber dari Disdukcapil.</p>
                    <ul class="ds-domain__list">
                        <li>NIK unik nasional</li>
                        <li>Relasi KK ? Penduduk</li>
                        <li>Import dari Dukcapil</li>
                    </ul>
                </article>
                <article class="ds-domain">
                    <div class="ds-domain__num">02</div>
                    <h3 class="ds-heading ds-heading--md">Data Sektoral</h3>
                    <p class="ds-text ds-text--muted ds-mt-2">Dataset generic untuk 32 OPD. Konfigurasi field dinamis.</p>
                    <ul class="ds-domain__list">
                        <li>Periode fleksibel</li>
                        <li>Field schema per-OPD</li>
                        <li>Validasi terpusat</li>
                    </ul>
                </article>
                <article class="ds-domain">
                    <div class="ds-domain__num">03</div>
                    <h3 class="ds-heading ds-heading--md">Data Lapangan</h3>
                    <p class="ds-text ds-text--muted ds-mt-2">Rumah, infrastruktur, fasilitas. Dikumpulkan Admin Desa.</p>
                    <ul class="ds-domain__list">
                        <li>Mobile offline-first</li>
                        <li>GIS multi-geometry</li>
                        <li>Verifikasi bertingkat</li>
                    </ul>
                </article>
                <article class="ds-domain">
                    <div class="ds-domain__num">04</div>
                    <h3 class="ds-heading ds-heading--md">Integrasi</h3>
                    <p class="ds-text ds-text--muted ds-mt-2">Statistik, peta publik, API. Untuk publik dan sistem lain.</p>
                    <ul class="ds-domain__list">
                        <li>REST API v1</li>
                        <li>Dashboard publik</li>
                        <li>Export PDF/Excel</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    {{-- STATS / TRUST --}}
    <section id="arsitektur" class="ds-section">
        <div class="ds-container">
            <div class="ds-card ds-card--elevated">
                <div class="ds-card-body ds-card-body--spacious">
                    <div class="ds-grid ds-grid--kpi">
                        <div>
                            <p class="ds-eyebrow">ARSITEKTUR</p>
                            <h2 class="ds-heading ds-heading--lg ds-mt-2">Dibangun dengan fondasi modern.</h2>
                            <p class="ds-text ds-text--muted ds-mt-4">
                                Laravel 10, Filament Admin, Spatie Permission, Flutter Mobile, REST API.
                                Dirancang untuk 100500 concurrent users, jutaan records, dan offline-first sync.
                            </p>
                        </div>
                        <div>
                            <ul class="ds-trust-list">
                                <li><x-heroicon-s-check-circle class="w-4 h-4"/> PostgreSQL/MySQL ready</li>
                                <li><x-heroicon-s-check-circle class="w-4 h-4"/> Redis caching layer</li>
                                <li><x-heroicon-s-check-circle class="w-4 h-4"/> Laravel Horizon queue</li>
                                <li><x-heroicon-s-check-circle class="w-4 h-4"/> Laravel Octane support</li>
                                <li><x-heroicon-s-check-circle class="w-4 h-4"/> Horizontal scaling</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer id="kontak" class="ds-landing__footer">
        <div class="ds-container">
            <div class="ds-landing__footer-grid">
                <div>
                    <a href="/" class="ds-landing__logo">
                        <div class="ds-landing__logo-mark">
                            <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect width="32" height="32" rx="7" fill="currentColor"/>
                                <path d="M9 16 L14 21 L23 12" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </div>
                        <div>
                            <p class="ds-landing__logo-text">SIPANDU</p>
                            <p class="ds-landing__logo-sub">Pangkalan Data Terpadu</p>
                        </div>
                    </a>
                    <p class="ds-text ds-text--muted ds-mt-4" style="max-width: 360px;">
                        Satu Data Indonesia untuk Kabupaten Kepulauan Sangihe.
                        Bersih, terstruktur, terverifikasi.
                    </p>
                </div>
                <div>
                    <p class="ds-eyebrow">SISTEM</p>
                    <ul class="ds-landing__footer-list">
                        <li><a href="{{ url('/admin') }}">Panel Admin</a></li>
                        <li><a href="{{ url('/up') }}">Health Check</a></li>
                        <li><a href="/api/v1/public">Public API</a></li>
                    </ul>
                </div>
                <div>
                    <p class="ds-eyebrow">DOKUMENTASI</p>
                    <ul class="ds-landing__footer-list">
                        <li><a href="#fitur">Fitur</a></li>
                        <li><a href="#domain">Domain</a></li>
                        <li><a href="#arsitektur">Arsitektur</a></li>
                    </ul>
                </div>
            </div>
            <div class="ds-landing__footer-bottom">
                <p class="ds-text ds-text--subtle ds-text--sm">
                    &copy; {{ date('Y') }} Pemerintah Kabupaten Kepulauan Sangihe. All rights reserved.
                </p>
                <p class="ds-text ds-text--subtle ds-text--sm">
                    v1.0.0 · SIPANDU
                </p>
            </div>
        </div>
    </footer>
</body>
</html>

<style>
.ds-landing {
    background: var(--ds-ink-50);
    color: var(--ds-ink-900);
    min-height: 100vh;
    font-family: var(--ds-font-sans);
}

/* Topbar */
.ds-landing__topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: saturate(180%) blur(12px);
    -webkit-backdrop-filter: saturate(180%) blur(12px);
    border-bottom: 1px solid var(--ds-ink-200);
}

.ds-landing__logo {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: var(--ds-ink-900);
}

.ds-landing__logo-mark {
    width: 36px;
    height: 36px;
    color: var(--ds-primary-600);
}

.ds-landing__logo-mark svg { width: 100%; height: 100%; }

.ds-landing__logo-text {
    font-family: var(--ds-font-display);
    font-weight: 800;
    font-size: 0.95rem;
    letter-spacing: -0.02em;
    line-height: 1;
    color: var(--ds-ink-900);
}

.ds-landing__logo-sub {
    font-size: 0.7rem;
    color: var(--ds-ink-500);
    line-height: 1.2;
    margin-top: 2px;
}

.ds-landing__nav {
    display: flex;
    align-items: center;
    gap: 1.75rem;
}

.ds-landing__nav-link {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--ds-ink-600);
    text-decoration: none;
    transition: color var(--ds-transition-fast);
}

.ds-landing__nav-link:hover { color: var(--ds-ink-900); }

@media (max-width: 768px) {
    .ds-landing__nav { display: none; }
}

/* HERO LANDING */
.ds-hero--landing {
    border-radius: 0;
    min-height: 80vh;
    padding: var(--ds-space-20) 0;
    background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
}

.ds-hero__bg--landing {
    background:
        radial-gradient(ellipse at 30% 20%, rgba(99,102,241,0.25) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 60%, rgba(14,165,233,0.18) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 100%, rgba(124,58,237,0.12) 0%, transparent 60%);
}

.ds-hero__content--landing {
    position: relative;
    z-index: 2;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}

.ds-hero__title--landing {
    font-size: 3.5rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.05;
    color: white;
    margin: var(--ds-space-6) 0 var(--ds-space-4);
    max-width: 900px;
}

.ds-hero__title-accent {
    display: block;
    background: linear-gradient(90deg, #818cf8 0%, #38bdf8 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    margin-top: var(--ds-space-2);
}

.ds-hero__subtitle--landing {
    color: rgba(255,255,255,0.7);
    font-size: 1.05rem;
    line-height: 1.7;
    max-width: 640px;
    margin: 0 auto var(--ds-space-8);
}

.ds-hero__actions {
    display: flex;
    gap: var(--ds-space-3);
    flex-wrap: wrap;
    justify-content: center;
}

.ds-hero__stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--ds-space-8);
    margin-top: var(--ds-space-12);
    padding-top: var(--ds-space-8);
    border-top: 1px solid rgba(255,255,255,0.10);
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
}

.ds-hero__stat {
    text-align: center;
}

.ds-hero__stat dt {
    font-family: var(--ds-font-display);
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: -0.04em;
    color: white;
    line-height: 1;
}

.ds-hero__stat dd {
    margin: 0;
    margin-top: 0.5rem;
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
}

@media (max-width: 768px) {
    .ds-hero__title--landing { font-size: 2.25rem; }
    .ds-hero__stats { grid-template-columns: repeat(2, 1fr); gap: var(--ds-space-4); }
    .ds-hero__stat dt { font-size: 1.75rem; }
}

/* Section */
.ds-section {
    padding: var(--ds-space-20) 0;
}
.ds-section--alt {
    background: var(--ds-ink-100);
}

.ds-section-header {
    margin-bottom: var(--ds-space-10);
}
.ds-section-header .ds-eyebrow { color: var(--ds-primary-600); }

/* Domain grid */
.ds-domain-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: var(--ds-space-5);
}

.ds-domain {
    background: var(--ds-ink-0);
    border: 1px solid var(--ds-ink-200);
    border-radius: var(--ds-radius);
    padding: var(--ds-space-6);
    transition: all var(--ds-transition);
    position: relative;
}

.ds-domain:hover {
    border-color: var(--ds-primary-300);
    box-shadow: var(--ds-shadow-md);
    transform: translateY(-2px);
}

.ds-domain__num {
    font-family: var(--ds-font-display);
    font-size: 0.75rem;
    font-weight: 800;
    color: var(--ds-primary-600);
    letter-spacing: 0.1em;
    margin-bottom: var(--ds-space-4);
}

.ds-domain__list {
    list-style: none;
    padding: 0;
    margin: var(--ds-space-4) 0 0;
}

.ds-domain__list li {
    padding: 0.5rem 0;
    font-size: 0.875rem;
    color: var(--ds-ink-700);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-top: 1px solid var(--ds-ink-100);
}

.ds-domain__list li::before {
    content: "";
    width: 4px;
    height: 4px;
    border-radius: 9999px;
    background: var(--ds-primary-500);
    flex-shrink: 0;
}

/* Trust list */
.ds-trust-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--ds-space-3);
}

.ds-trust-list li {
    display: flex;
    align-items: center;
    gap: var(--ds-space-3);
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--ds-ink-800);
}

.ds-trust-list li svg { color: var(--ds-success-600); flex-shrink: 0; }

/* Footer */
.ds-landing__footer {
    background: var(--ds-ink-900);
    color: var(--ds-ink-300);
    padding: var(--ds-space-16) 0 var(--ds-space-8);
}

.ds-landing__footer .ds-landing__logo-text { color: white; }
.ds-landing__footer .ds-landing__logo-sub { color: var(--ds-ink-500); }
.ds-landing__footer .ds-landing__logo-mark { color: var(--ds-primary-400); }

.ds-landing__footer-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: var(--ds-space-12);
    padding-bottom: var(--ds-space-12);
    border-bottom: 1px solid var(--ds-ink-800);
}

.ds-landing__footer .ds-eyebrow {
    color: var(--ds-ink-500);
    margin-bottom: var(--ds-space-4);
}

.ds-landing__footer-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: var(--ds-space-2);
}

.ds-landing__footer-list a {
    color: var(--ds-ink-300);
    text-decoration: none;
    font-size: 0.875rem;
    transition: color var(--ds-transition-fast);
}

.ds-landing__footer-list a:hover { color: white; }

.ds-landing__footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--ds-space-6);
}

@media (max-width: 768px) {
    .ds-landing__footer-grid { grid-template-columns: 1fr; gap: var(--ds-space-8); }
    .ds-landing__footer-bottom { flex-direction: column; gap: var(--ds-space-3); }
}

.ds-text--sm { font-size: 0.8125rem; }
</style>
