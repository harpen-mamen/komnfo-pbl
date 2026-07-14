<x-filament::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Batch</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $this->record->submission_number }}</div>
                <div class="mt-1 text-sm text-slate-600">{{ \App\Models\SubmissionBatch::moduleLabel($this->record->module) }}</div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Status</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ \App\Models\SubmissionBatch::statusLabel($this->record->status) }}</div>
                <div class="mt-4 text-sm text-slate-500">Periode</div>
                <div class="mt-1 text-base font-medium text-slate-800">
                    @if ($this->record->module === \App\Models\SubmissionBatch::MODULE_KESEHATAN)
                        Bulan {{ $this->record->periode_bulan ?? '-' }} / {{ $this->record->periode_tahun ?? '-' }}
                    @elseif ($this->record->module === \App\Models\SubmissionBatch::MODULE_PENDIDIKAN)
                        Semester {{ ucfirst($this->record->semester ?? '-') }} / {{ $this->record->periode_tahun ?? '-' }}
                    @else
                        Master Data
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-slate-500">Pengirim</div>
                <div class="mt-2 text-lg font-semibold text-slate-900">{{ $this->record->createdBy?->name ?? '-' }}</div>
                <div class="mt-4 text-sm text-slate-500">Tanggal Kirim</div>
                <div class="mt-1 text-base font-medium text-slate-800">{{ $this->record->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Ringkasan Batch</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->getEntitySummary() as $item)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="text-sm text-slate-500">{{ $item['label'] }}</div>
                        <div class="mt-1 text-base font-semibold text-slate-900">{{ $item['value'] ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Isi Batch</h2>
                    <p class="mt-1 text-sm text-slate-500">Kominfo melihat seluruh data di dalam batch ini berdasarkan modul.</p>
                </div>
                <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-600">
                    {{ count($this->getBatchItems()) }} item
                </div>
            </div>

            <div class="mt-4 space-y-5">
                @forelse ($this->getBatchItemsGroupedByModule() as $group)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="text-sm font-semibold uppercase tracking-[0.16em] text-slate-700">{{ $group['label'] }}</h3>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $group['count'] }} item</span>
                        </div>

                        <div class="mt-4 grid gap-4 xl:grid-cols-2">
                            @foreach ($group['items'] as $item)
                                <article class="rounded-xl border border-slate-200 bg-white p-4">
                                    <div>
                                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $item['type'] }}</div>
                                        <h4 class="mt-1 text-base font-semibold text-slate-900">{{ $item['title'] }}</h4>
                                        <p class="mt-1 text-sm text-slate-600">{{ $item['subtitle'] ?? '-' }}</p>
                                    </div>

                                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                                        @foreach ($item['fields'] as $field)
                                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">{{ $field['label'] }}</dt>
                                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $field['value'] ?? '-' }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        Belum ada item pada batch ini.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Snapshot Pengiriman</h2>
            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                @foreach ($this->getSnapshotModules() as $snapshot)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $snapshot['label'] }}</div>
                            <div class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $snapshot['count'] }} item</div>
                        </div>
                        <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Bulan</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $snapshot['periode_bulan'] ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Semester</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $snapshot['semester'] ? ucfirst($snapshot['semester']) : '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-[0.14em] text-slate-400">Tahun</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $snapshot['periode_tahun'] ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Riwayat Verifikasi</h2>
            <div class="mt-4 space-y-3">
                @forelse ($this->getTimeline() as $item)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                {{ $item['status_baru'] ?? '-' }}
                            </span>
                            <span class="text-sm text-slate-500">{{ optional($item['waktu'])->format('d M Y H:i') }}</span>
                        </div>
                        <div class="mt-2 text-sm text-slate-900">
                            {{ $item['catatan'] ?? '-' }}
                        </div>
                        <div class="mt-1 text-xs text-slate-500">
                            {{ $item['dilakukan_oleh'] ?? '-' }}
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        Belum ada riwayat workflow.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Komentar Verifikasi</h2>
            <div class="mt-4 space-y-3">
                @forelse ($this->getVerificationComments() as $comment)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-700">
                                {{ $comment['tipe'] ?? '-' }}
                            </span>
                            <span class="text-sm text-slate-500">{{ $comment['created_at']?->format('d M Y H:i') }}</span>
                        </div>
                        <div class="mt-2 text-sm text-slate-900">{{ $comment['komentar'] ?? '-' }}</div>
                        <div class="mt-1 text-xs text-slate-500">{{ $comment['pengguna'] ?? '-' }}</div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-500">
                        Belum ada komentar verifikasi.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament::page>
