<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h2 class="text-2xl font-bold text-white">📊 Laporan & Ekspor Data</h2>
            </div>
            <p class="text-slate-600 dark:text-slate-300">Akses berbagai laporan dan unduh data dalam format yang tersedia</p>
        </div>

        <!-- Reports Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($reports as $report)
                <a
                    href="{{ $report['url'] }}"
                    target="_blank"
                    class="card-kominfo group hover:shadow-xl transition-all"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors">
                                {{ $report['label'] }}
                            </h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                Klik untuk membuka laporan
                            </p>
                        </div>
                        <div class="text-2xl text-green-600 dark:text-green-400 opacity-20 group-hover:opacity-40 transition-opacity">
                            📄
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if (empty($reports))
            <div class="card-kominfo text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-slate-500 dark:text-slate-400">{{ $notice }}</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
