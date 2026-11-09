<x-filament-panels::page>
    <div class="space-y-6">
        <header class="rounded-xl bg-navy p-6 text-white">
            <a href="{{ \App\Filament\Pages\DataOpd::getUrl() }}" class="text-sm text-brand-200 hover:underline">Kembali ke Katalog Dataset</a>
            <h1 class="mt-3 text-2xl font-bold">{{ $dataset->nama }}</h1>
            <p class="mt-2 text-sm text-slate-300">{{ $opd?->nama }} &middot; {{ $dataset->kode }}</p>
            @if ($dataset->deskripsi)<p class="mt-2 text-sm leading-6 text-slate-300">{{ $dataset->deskripsi }}</p>@endif
        </header>
        <div class="flex flex-wrap gap-3">
            @can('create', \App\Models\DatasetValue::class)
                <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('create', ['dataset'=>$dataset->kode]) }}" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white">Input Data</a>
            @endcan
            <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('index', ['dataset'=>$dataset->kode]) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold">Lihat Data</a>
            <a href="{{ route('dataset.export', ['kode'=>$dataset->kode,'format'=>'csv']) }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold">Unduh CSV</a>
        </div>
        <section class="grid grid-cols-2 gap-4">
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Catatan tersimpan</p><p class="mt-2 text-2xl font-bold text-navy">{{ $total_values }}</p><p class="mt-1 text-xs text-slate-500">Jumlah baris data, bukan jumlah objek.</p></div>
            <div class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm text-slate-500">Catatan diterbitkan</p><p class="mt-2 text-2xl font-bold text-brand-700">{{ $published_count }}</p><p class="mt-1 text-xs text-slate-500">Hanya catatan ini yang dipakai di infografis.</p></div>
        </section>
        @if (data_get($dataset->config, 'source_publication'))
            <section class="rounded-xl border border-brand-200 bg-brand-50 p-5 text-sm leading-6 text-slate-700">
                <h2 class="font-bold">Sumber data</h2>
                <p>{{ data_get($dataset->config,'source_publication') }} &middot; tabel {{ data_get($dataset->config,'source_table') }}, halaman {{ data_get($dataset->config,'source_page') }}</p>
                <a href="{{ data_get($dataset->config,'source_url') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-brand-800 underline">Buka publikasi sumber</a>
                <p class="mt-2">Angka mengikuti tabel sumber. Data kosong tidak berarti nol. Koordinat hanya ditampilkan jika tersedia sumber lokasi.</p>
            </section>
        @endif
        <section class="space-y-4">
            <div>
                <h2 class="text-lg font-bold text-navy">Apa yang ditunjukkan data ini?</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Tahun {{ implode(', ', $context['years']) ?: 'belum tersedia' }}. Setiap kartu menjelaskan satu indikator atau kelompok rincian dengan satuan yang sama.</p>
            </div>
            @if ((string)$dataset->kode === '5.59')
                <p class="rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm leading-6 text-brand-900">Panjang jalan memakai km, jumlah jembatan memakai unit, dan panjang jembatan memakai m. Rincian permukaan dan kondisi adalah bagian dari jalan kabupaten yang sama, bukan tambahan panjang jalan.</p>
            @endif
            <div class="grid items-start gap-4 xl:grid-cols-2">
                @forelse ($statistics as $chart)
                    <x-infographic-chart :chart="$chart" :variant="$loop->index" />
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-sm text-slate-500">Belum ada data terbit untuk ditampilkan. Data kosong tidak berarti nol.</p>
                @endforelse
            </div>
            <p class="text-xs leading-6 text-slate-500">Sumber: {{ $context['source'] }}@if ($context['source_table']), tabel {{ $context['source_table'] }}@endif @if ($context['source_page']), halaman {{ $context['source_page'] }}@endif.</p>
        </section>
        <details class="rounded-xl border border-slate-200 bg-white p-5">
            <summary class="cursor-pointer font-semibold text-slate-700">Struktur dan definisi field</summary>
            <dl class="mt-4 space-y-3">@foreach ($fields as $field)<div><dt class="text-sm font-semibold">{{ $field->label }} <span class="font-normal text-slate-500">({{ $field->tipe }})</span></dt><dd class="mt-1 text-xs leading-5 text-slate-500">{{ $field->definisi ?: ($field->satuan ?: 'Belum ada definisi tambahan') }}</dd></div>@endforeach</dl>
        </details>
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-slate-800">Catatan sumber terbaru</h2>
            @forelse ($recent_values->pluck('notes')->filter()->unique() as $note)
                <p class="mt-3 break-words text-xs leading-6 text-slate-600">{{ preg_replace('/^\[SANGIHE-SOURCE:[^\]]+\]\s*/', '', $note) }}</p>
            @empty
                <p class="mt-3 text-sm text-slate-500">Belum ada catatan sumber.</p>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
