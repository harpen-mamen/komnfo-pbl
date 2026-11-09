<x-filament-panels::page>
    <div class="space-y-6">
        <header class="rounded-2xl bg-navy p-6 text-white sm:p-8">
            <a href="{{ \App\Filament\Pages\DataOpd::getUrl() }}" class="text-sm font-medium text-brand-200 hover:underline">Kembali ke Katalog Dataset</a>
            <p class="mt-5 text-xs font-semibold uppercase tracking-widest text-brand-200">Data dalam angka</p>
            <h1 class="mt-2 text-2xl font-bold leading-tight sm:text-3xl">{{ $opd->nama }}</h1>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-300">Baca setiap indikator beserta satuan dan tahunnya. Indikator yang berbeda tidak dijumlahkan sebagai satu total.</p>
        </header>
        <section class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div>
                <h2 class="font-bold text-slate-900">{{ count($datasets) }} dataset memiliki data terbit</h2>
                <p class="mt-1 text-sm text-slate-500">Data kosong tidak berarti nol. Sumber dan catatan tersedia pada setiap dataset.</p>
            </div>
            <div>
                <label for="infographic-year" class="mb-1 block text-xs font-semibold text-slate-600">Tahun data</label>
                <select id="infographic-year" wire:model.live="selectedYear" class="rounded-lg border-slate-300 text-sm focus:border-brand-600 focus:ring-brand-600">
                    <option value="">Semua tahun yang tersedia</option>
                    @foreach ($availableYears as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
                </select>
                <span wire:loading.delay wire:target="selectedYear" role="status" class="ml-2 text-xs text-slate-500">Memuat...</span>
            </div>
        </section>
        @if (count($datasets) > 1)
            <nav aria-label="Pilih bagian infografis" class="flex flex-wrap gap-2">
                @foreach ($datasets as $item)<a href="#dataset-{{ $item['dataset']->id }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:border-brand-500 hover:text-brand-800">{{ $item['dataset']->nama }}</a>@endforeach
            </nav>
        @endif
        @forelse ($datasets as $item)
            <section wire:key="dataset-{{ $item['dataset']->id }}-{{ $selectedYear }}" id="dataset-{{ $item['dataset']->id }}" class="scroll-mt-24 space-y-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-6">
                <header class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-700">Dataset {{ $item['dataset']->kode }}</p>
                        <h2 class="mt-1 text-xl font-bold text-navy">{{ (string)$item['dataset']->kode === '5.59' ? 'Jalan dan jembatan: panjang, permukaan, dan kondisi' : $item['dataset']->nama }}</h2>
                        <p class="mt-2 text-sm text-slate-600">Tahun {{ implode(', ', $item['context']['years']) }} &middot; {{ $item['published_count'] }} catatan terbit <span class="text-slate-400">(jumlah catatan, bukan jumlah objek)</span></p>
                    </div>
                    <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('index',['dataset'=>$item['dataset']->kode]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:border-brand-500">Buka data sumber</a>
                </header>
                @if ((string)$item['dataset']->kode === '5.59')
                    <div class="rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm leading-6 text-brand-900"><strong>Cara membaca:</strong> panjang jalan memakai kilometer (km), jumlah jembatan memakai unit, dan panjang jembatan memakai meter (m). Rincian permukaan dan kondisi adalah dua cara membaca jalan kabupaten yang sama, bukan panjang jalan tambahan.</div>
                @endif
                @if ($item['context']['is_location_reference'])
                    <p class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Tahun di sini adalah tahun akses sumber, bukan tahun pembangunan atau survei. Lokasi bersifat rujukan publik; fasilitas tanpa koordinat yang layak tidak ditempatkan pada peta.</p>
                @endif
                <div class="grid items-start gap-4 xl:grid-cols-2">
                    @forelse ($item['charts'] as $chart)<x-infographic-chart :chart="$chart" :variant="$loop->index" />@empty
                        <p class="rounded-xl border border-dashed border-slate-300 p-6 text-sm text-slate-500">Belum ada indikator numerik untuk divisualisasikan. Gunakan data sumber untuk membaca keterangannya.</p>
                    @endforelse
                </div>
                @if (count($item['map_features'] ?? []))
                    <div class="rounded-xl border border-slate-200 bg-white p-5">
                        <h3 class="font-bold text-slate-800">Lokasi yang memiliki koordinat</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Hanya geometri yang tersedia dalam data terbit. Titik bukan batas bangunan atau hasil verifikasi lapangan.</p>
                        <div wire:ignore wire:key="map-{{ $item['dataset']->id }}-{{ $selectedYear }}" data-map-features="{{ json_encode($item['map_features']) }}" class="mt-4 h-80 w-full rounded-xl bg-slate-100" id="opd-map-{{ $item['dataset']->id }}"><p class="p-4 text-sm text-slate-500">Memuat peta. Jika peta tidak tersedia, lokasi tetap dapat dibaca pada data sumber.</p></div>
                    </div>
                @endif
                <footer class="rounded-xl border border-slate-200 bg-white p-4 text-xs leading-6 text-slate-600">
                    <p><span class="font-semibold text-slate-800">Sumber:</span> {{ $item['context']['source'] }}@if ($item['context']['source_table']), tabel {{ $item['context']['source_table'] }}@endif @if ($item['context']['source_page']), halaman {{ $item['context']['source_page'] }}@endif.</p>
                    @if ($item['context']['source_url'])<a href="{{ $item['context']['source_url'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand-800 underline">Buka publikasi sumber</a>@endif
                    @if ($item['context']['notes'])
                        <details class="mt-2"><summary class="cursor-pointer font-semibold">Catatan dan batasan data</summary><div class="mt-2 space-y-2">@foreach ($item['context']['notes'] as $note)<p class="break-words">{{ $note }}</p>@endforeach</div></details>
                    @endif
                </footer>
            </section>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center"><h2 class="font-semibold text-slate-700">Belum ada data terbit untuk pilihan ini</h2><p class="mt-2 text-sm text-slate-500">Coba tahun lain. Data yang belum tersedia tidak ditampilkan sebagai nol.</p></div>
        @endforelse
    </div>
    @include('filament.partials.infographic-maps')
</x-filament-panels::page>
