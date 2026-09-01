<x-filament::page>
    <div class="space-y-4">
        <!-- Header -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h2 class="text-2xl font-bold text-white">📊 Monitoring Kabupaten/Kecamatan</h2>
            </div>
            <p class="text-slate-600 dark:text-slate-300">Ringkasan data desa, rumah, keluarga, dan penduduk per wilayah administratif</p>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                <thead class="bg-gradient-to-r from-slate-700 to-slate-800 dark:from-slate-900 dark:to-slate-800">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-white">Wilayah</th>
                        <th class="px-6 py-4 text-left font-semibold text-white">Jumlah Desa</th>
                        <th class="px-6 py-4 text-left font-semibold text-white">Total Rumah</th>
                        <th class="px-6 py-4 text-left font-semibold text-white">Total Keluarga</th>
                        <th class="px-6 py-4 text-left font-semibold text-white">Total Penduduk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse ($this->rows as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">{{ $row['nama'] }}</td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                    {{ $row['desa'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                    {{ $row['rumah'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200">
                                    {{ $row['keluarga'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
                                    {{ $row['penduduk'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Belum ada data kecamatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
