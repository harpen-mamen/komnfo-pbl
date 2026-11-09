<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm leading-6 text-slate-600">Pilih dataset untuk melihat nilai, sumber, infografis, atau memasukkan data sesuai kewenangan Anda.</p>
            <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                <div><p class="text-slate-500">Dataset</p><p class="mt-1 text-xl font-bold text-navy">{{ $totalDatasets }}</p></div>
                <div><p class="text-slate-500">Data tersimpan</p><p class="mt-1 text-xl font-bold text-navy">{{ $totalValues }}</p></div>
                <div><p class="text-slate-500">Data terbit</p><p class="mt-1 text-xl font-bold text-navy">{{ $totalPublished }}</p></div>
            </div>
            <label for="dataset-search" class="mt-5 block text-sm font-semibold text-slate-700">Cari dataset atau OPD</label>
            <input id="dataset-search" type="search" wire:model.debounce.350ms="search" placeholder="Contoh: stunting, jalan, pertanian" class="mt-2 w-full rounded-lg border-slate-300 focus:border-brand-600 focus:ring-brand-600">
            <p wire:loading.delay wire:target="search" role="status" class="mt-2 text-xs text-slate-500">Mencari dataset...</p>
        </div>
        @forelse ($grouped as $opdName => $datasets)
            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                    <div><h2 class="font-bold text-navy">{{ $opdName }}</h2><p class="mt-1 text-xs text-slate-500">{{ $datasets->count() }} dataset</p></div>
                    <a href="{{ \App\Filament\Pages\InfografisOpd::getUrl(['opd' => $datasets->first()->opd_id]) }}" class="text-sm font-semibold text-brand-700 hover:underline">Infografis OPD</a>
                </header>
                <div class="divide-y divide-slate-100">
                    @foreach ($datasets as $dataset)
                        <article class="flex flex-col justify-between gap-4 p-5 lg:flex-row lg:items-center">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2"><span class="rounded bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $dataset->kode }}</span><h3 class="text-sm font-bold text-slate-800">{{ $dataset->nama }}</h3></div>
                                <p class="mt-2 text-xs leading-5 text-slate-500">{{ $dataset->fields_count }} field &middot; {{ $dataset->values_count }} data tersimpan &middot; {{ $dataset->published_count }} terbit &middot; {{ ucfirst($dataset->period_type) }}</p>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('index', ['dataset' => $dataset->kode]) }}" class="rounded-lg bg-brand-700 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-800">Lihat Data</a>
                                <a href="{{ \App\Filament\Pages\InfografisDataset::getUrl(['kode' => $dataset->kode]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-brand-500">Infografis</a>
                                @if ($canCreate)
                                    <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('create', ['dataset' => $dataset->kode]) }}" class="rounded-lg border border-brand-300 px-3 py-2 text-xs font-semibold text-brand-800 hover:bg-brand-50">Input Data</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center"><p class="font-semibold text-slate-700">Dataset tidak ditemukan</p><p class="mt-2 text-sm text-slate-500">Coba kata kunci lain atau periksa OPD yang dapat diakses.</p></div>
        @endforelse
    </div>
</x-filament-panels::page>
