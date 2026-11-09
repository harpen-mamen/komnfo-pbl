<x-filament-panels::page>
    <div class="space-y-6 text-slate-800">
        <header class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-navy via-slate-800 to-brand-800 px-6 py-7 text-white shadow-panel sm:px-8">
            <div class="relative z-10 flex flex-col justify-between gap-6 sm:flex-row sm:items-center">
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-brand-200">{{ $dashboard['header']['badge'] }}</p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $dashboard['header']['title'] }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-200">{{ $dashboard['header']['subtitle'] }}</p>
                </div>
                <div class="shrink-0 sm:text-right">
                    <p class="text-sm text-slate-300">{{ $dashboard['header']['date'] }}</p>
                    <p class="mt-1 text-xl font-bold tabular-nums text-white">{{ $dashboard['header']['time'] }} WITA</p>
                    <span class="mt-2 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>{{ $dashboard['header']['login_status'] }}
                    </span>
                </div>
            </div>
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-brand-300/20 blur-3xl"></div>
        </header>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Ringkasan statistik">
            @foreach ($dashboard['kpis'] as $kpi)
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-panel sm:p-5">
                    <p class="text-sm font-semibold text-slate-500">{{ $kpi['label'] }}</p>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-navy sm:text-3xl">{{ number_format((int) $kpi['value'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs leading-5 text-slate-500">{{ $kpi['description'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-panel">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-navy">Data per OPD</h2>
                <p class="mt-1 text-sm text-slate-500">Buka infografis atau tinjau data yang sudah diterbitkan.</p>
            </div>
            <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6 xl:grid-cols-3">
                @forelse ($dashboard['opds'] as $opd)
                    <article class="flex min-w-0 flex-col rounded-xl border border-slate-200 bg-slate-50/50 p-5 transition hover:border-brand-300 hover:bg-white hover:shadow-panel">
                        <div class="flex items-start justify-between gap-3">
                            <span class="truncate text-xs font-bold uppercase tracking-wider text-brand-700">{{ $opd['kode'] ?: 'OPD' }}</span>
                            <span @class([
                                'shrink-0 rounded-full px-2.5 py-1 text-[0.7rem] font-bold',
                                'bg-emerald-50 text-emerald-700' => $opd['jumlah_terbit'] > 0,
                                'bg-slate-200 text-slate-600' => $opd['jumlah_terbit'] === 0,
                            ])>{{ $opd['jumlah_terbit'] > 0 ? 'Ada data terbit' : 'Belum ada data' }}</span>
                        </div>
                        <h3 class="mt-3 text-base font-bold leading-6 text-slate-800">{{ $opd['nama'] }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $opd['jumlah_dataset'] }} dataset aktif &middot; {{ $opd['jumlah_terbit'] }} data terbit</p>
                        <div class="mt-auto flex flex-wrap gap-2 pt-5">
                            <a class="inline-flex min-h-[38px] items-center rounded-lg bg-brand-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-brand-800" href="{{ url('/admin/infografis-opd/'.$opd['id']) }}">Lihat Infografis</a>
                            <a class="inline-flex min-h-[38px] items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-brand-400 hover:text-brand-800" href="{{ url('/admin/data-opd/'.$opd['id']) }}">Lihat Data</a>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full py-10 text-center">
                        <p class="font-semibold text-slate-700">Belum ada OPD</p>
                        <p class="mt-1 text-sm text-slate-500">Data OPD yang dapat diakses akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-panel">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="text-lg font-bold text-navy">Dataset terbaru</h2>
                <p class="mt-1 text-sm text-slate-500">Dataset aktif yang terakhir ditambahkan.</p>
            </div>
            @if ($dashboard['recent_datasets']->isEmpty())
                <div class="py-10 text-center"><p class="font-semibold text-slate-700">Belum ada dataset</p><p class="mt-1 text-sm text-slate-500">Dataset aktif akan ditampilkan setelah ditambahkan.</p></div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($dashboard['recent_datasets'] as $dataset)
                        <div class="flex flex-col justify-between gap-2 px-5 py-4 sm:flex-row sm:items-center sm:px-6">
                            <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-800">{{ $dataset['nama'] }}</p><p class="mt-0.5 truncate text-xs text-slate-500">{{ $dataset['opd'] }}</p></div>
                            <p class="shrink-0 text-xs font-medium text-slate-500">{{ $dataset['jumlah_field'] }} field &middot; {{ $dataset['jumlah_nilai'] }} nilai</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <footer class="flex flex-col justify-between gap-2 px-1 text-xs text-slate-500 sm:flex-row">
            <span>Terakhir diperbarui: {{ $dashboard['summary']['date'] }} {{ $dashboard['summary']['time'] }} WITA</span>
            <span>Server {{ $dashboard['summary']['server_status'] }} &middot; Database {{ $dashboard['summary']['database_status'] }}</span>
        </footer>
    </div>
</x-filament-panels::page>
