<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-6 text-white">
        <div>
            <a href="{{ url('/admin/data-opd') }}" class="inline-flex items-center gap-1 text-sm font-medium text-cyan-300 hover:text-cyan-200 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Semua OPD
            </a>
            <div class="mt-2 flex items-center gap-2">
                <span class="rounded bg-cyan-400/20 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-cyan-300">{{ $opd->singkatan ?? $opd->kode }}</span>
                <span class="text-xs text-slate-400">{{ $opd->kategori ?? '' }}</span>
            </div>
            <h1 class="mt-3 text-2xl font-bold">Data Mentah {{ $opd->nama }}</h1>
            <p class="mt-2 text-sm text-slate-300">Nilai data yang telah diterbitkan, ditampilkan tanpa agregasi statistik.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ url('/admin/data-opd/'.$opd->id) }}" class="rounded-lg bg-cyan-400 px-4 py-2.5 text-sm font-bold text-slate-950 hover:bg-cyan-300 transition">
                Lihat Data
            </a>
            <a href="{{ url('/admin/infografis-opd/'.$opd->id) }}" class="rounded-lg border border-cyan-400/50 px-4 py-2.5 text-sm font-bold text-cyan-300 hover:bg-cyan-400/10 transition">
                Lihat Infografis
            </a>
        </div>
    </div>

    {{-- Empty State --}}
    @if($datasets->isEmpty())
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-8 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-yellow-100">
                <svg class="h-7 w-7 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-yellow-800">Dataset Belum Tersedia</h3>
            <p class="mt-2 text-sm text-yellow-700 max-w-md mx-auto">
                Dataset untuk OPD <strong>{{ $opd->nama }}</strong> belum ditambahkan ke sistem.
            </p>
            <a href="{{ url('/admin/data-opd') }}" class="mt-6 inline-block rounded-lg bg-yellow-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-yellow-700 transition">
                Kembali ke Daftar OPD
            </a>
        </div>
    @else
        {{-- Dataset Grid --}}
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($datasets as $dataset)
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Data Mentah</p><h2 class="mt-2 text-base font-bold text-slate-900">{{ $dataset->nama }}</h2></div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $dataset->values->count() }} data terbit</span>
                    </div>
                    @if($dataset->values->isEmpty())
                        <p class="mt-4 text-sm text-slate-500">Belum ada data yang diterbitkan.</p>
                    @else
                        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50"><tr><th class="px-3 py-2 text-left font-semibold text-slate-600">Tahun</th><th class="px-3 py-2 text-left font-semibold text-slate-600">Periode</th>@foreach($dataset->fields as $field)<th class="px-3 py-2 text-left font-semibold text-slate-600">{{ $field->label }}</th>@endforeach</tr></thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($dataset->values as $record)
                                        <tr><td class="whitespace-nowrap px-3 py-2">{{ $record->tahun ?? '—' }}</td><td class="whitespace-nowrap px-3 py-2">{{ $record->periode_label ?? '—' }}</td>@foreach($dataset->fields as $field)<td class="px-3 py-2">{{ is_scalar(data_get($record->values, $field->nama)) ? data_get($record->values, $field->nama) : json_encode(data_get($record->values, $field->nama), JSON_UNESCAPED_UNICODE) }}</td>@endforeach</tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    @endif
</div>
