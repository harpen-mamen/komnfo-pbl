<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-slate-900">Kiriman titik lapangan dari aplikasi mobile</h2>
            <p class="mt-2 max-w-4xl text-sm leading-6 text-slate-600">
                Petugas mengirim titik lokasi rumah atau infrastruktur beserta koordinatnya. Untuk titik rumah, keluarga dipilih dari data OPD yang sudah terbit untuk desa tersebut. Sistem menyimpan tautan ke data keluarga itu, bukan menyalin ulang data keluarganya.
            </p>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Tinjau kiriman pada daftar batch. Kecamatan dapat memulai verifikasi dan meneruskannya ke Kominfo dari halaman detail batch.
            </p>
            @if ($kecamatan)
                <p class="mt-3 text-xs font-medium text-slate-500">Wilayah tugas: {{ $kecamatan }}</p>
            @endif
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">Titik tersinkron</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalPoints }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-800">Menunggu tinjauan kecamatan</p>
                <p class="mt-1 text-2xl font-bold text-amber-950">{{ $pendingBatches }}</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-sm text-blue-800">Sedang ditinjau</p>
                <p class="mt-1 text-2xl font-bold text-blue-950">{{ $inReviewBatches }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-sm text-emerald-800">Sudah diteruskan ke Kominfo</p>
                <p class="mt-1 text-2xl font-bold text-emerald-950">{{ $forwardedBatches }}</p>
            </div>
        </div>

        @if (! $hasVillages)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Kecamatan ini belum memiliki daftar desa. Hubungkan desa ke kecamatan agar titik mobile dapat ditampilkan.
            </div>
        @else
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="font-semibold text-slate-900">Titik mobile terbaru</h3>
                    <p class="mt-1 text-sm text-slate-500">Menampilkan paling banyak 50 titik terbaru di wilayah tugas Anda.</p>
                </div>
                @if ($points->isEmpty())
                    <div class="p-6 text-sm text-slate-600">Belum ada titik mobile yang tersinkron dari desa di kecamatan ini.</div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($points as $point)
                            @php
                                $batch = $point->submissionBatch;
                                $statusLabel = $batch?->forwarded_at
                                    ? 'Diteruskan ke Kominfo'
                                    : ($batch ? \App\Models\SubmissionBatch::statusLabel($batch->status) : 'Belum masuk batch');
                                $typeLabel = match ($point->jenis) {
                                    'rumah' => 'Rumah',
                                    'jalan' => 'Jalan',
                                    'jembatan' => 'Jembatan',
                                    'drainase' => 'Drainase',
                                    'lampu_jalan' => 'Lampu jalan',
                                    'air_bersih' => 'Air bersih',
                                    default => 'Infrastruktur lain',
                                };
                            @endphp
                            <article class="flex flex-col gap-3 p-5 lg:flex-row lg:items-center lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-semibold text-slate-900">{{ $point->nama }}</h4>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $typeLabel }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $statusLabel }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">Desa {{ $point->desa?->nama ?? '-' }} · {{ $point->alamat ?: 'Alamat tidak dicantumkan' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Koordinat: {{ $point->latitude }}, {{ $point->longitude }} · Keluarga tertaut dari data OPD: {{ count($point->keluarga_dataset_value_ids ?? []) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Diterima {{ $batch?->submitted_at?->format('d M Y H:i') ?? $point->created_at?->format('d M Y H:i') ?? '-' }}</p>
                                </div>
                                @if ($point->submission_batch_id)
                                    <a href="{{ \App\Filament\Resources\SubmissionBatchResource::getUrl('view', ['record' => $point->submission_batch_id]) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-800">
                                        Buka tinjauan batch
                                    </a>
                                @else
                                    <span class="shrink-0 text-xs text-slate-500">Batch belum tersedia</span>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
