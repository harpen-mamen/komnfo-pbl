<x-filament::page>
    <div class="space-y-6">
        <!-- Form Section -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-bold text-white">➕ Tambah Kecamatan Baru</h3>
            </div>
            
            <form wire:submit.prevent="create" class="space-y-4">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-3 pt-4">
                    <x-filament::button type="submit" icon="heroicon-o-plus-circle" class="btn-kominfo-primary">
                        Tambah Kecamatan
                    </x-filament::button>
                </div>
            </form>
        </div>

        <!-- List Section -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-bold text-white">📋 Daftar Kecamatan</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-700 dark:to-slate-600 text-left border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Kabupaten</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Kecamatan</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($this->kecamatans as $kecamatan)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <td class="px-6 py-3 text-slate-700 dark:text-slate-300">{{ $kecamatan->kabupaten->nama }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $kecamatan->nama }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                        Aktif
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    Belum ada kecamatan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
