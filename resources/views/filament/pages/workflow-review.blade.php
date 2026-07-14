<x-filament::page>
    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Jenis Data</div>
                <div class="mt-2 text-lg font-semibold text-gray-900">{{ $this->getEntityTypeLabel() }}</div>
                <div class="mt-1 text-sm text-gray-600">ID {{ $record->entity_id }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Workflow Status</div>
                <div class="mt-2 text-lg font-semibold text-gray-900">{{ $record->status?->nama ?? '-' }}</div>
                <div class="mt-4 text-sm text-gray-500">Kecamatan</div>
                <div class="mt-1 text-base font-medium text-gray-800">{{ $record->kecamatan?->nama ?? '-' }}</div>
                <div class="mt-3 text-sm text-gray-500">Desa</div>
                <div class="mt-1 text-base font-medium text-gray-800">{{ $record->desa?->nama ?? '-' }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="text-sm text-gray-500">Submitted By</div>
                <div class="mt-2 text-lg font-semibold text-gray-900">{{ $record->dibuatOleh?->name ?? '-' }}</div>
                <div class="mt-4 text-sm text-gray-500">Submitted Date</div>
                <div class="mt-1 text-base font-medium text-gray-800">{{ $record->tanggal_submit?->format('d M Y H:i') ?? '-' }}</div>
                <div class="mt-4 text-sm text-gray-500">Verification Date</div>
                <div class="mt-1 text-base font-medium text-gray-800">{{ $record->tanggal_verifikasi?->format('d M Y H:i') ?? '-' }}</div>
            </div>
        </div>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Entity Details</h2>
            <p class="mt-2 text-sm text-gray-500">Data {{ $this->getEntityTypeLabel() }}</p>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($this->getEntitySummary() as $item)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="text-sm text-gray-500">{{ $item['label'] }}</div>
                        <div class="mt-1 text-base font-semibold text-gray-900">{{ $item['value'] ?? '-' }}</div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                        Detail data tidak tersedia.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Workflow Summary</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->getWorkflowSummary() as $item)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="text-sm text-gray-500">{{ $item['label'] }}</div>
                        <div class="mt-1 text-base font-semibold text-gray-900">{{ $item['value'] ?? '-' }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Approve History</h2>
            <div class="mt-4 space-y-3">
                @forelse ($this->getTimeline() as $item)
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-700">
                                {{ $item['jenis'] === 'history' ? 'Riwayat' : 'Catatan' }}
                            </span>
                            <span class="text-sm text-gray-500">{{ optional($item['waktu'])->format('d M Y H:i') }}</span>
                        </div>
                        <div class="mt-2 text-sm text-gray-900">
                            {{ $item['status_baru'] ?? $item['tipe'] ?? '-' }}
                        </div>
                        <div class="mt-1 text-sm text-gray-600">
                            {{ $item['catatan'] ?? $item['komentar'] ?? '-' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            {{ $item['dilakukan_oleh'] ?? '-' }}{{ ! empty($item['role']) ? ' • '.$item['role'] : '' }}
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">
                        Belum ada riwayat workflow.
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament::page>
