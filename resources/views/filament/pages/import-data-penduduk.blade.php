<x-filament::page>
    <div class="space-y-6">
        <!-- Upload Form -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-bold text-white">📥 Import Data Penduduk</h3>
            </div>
            
            <form wire:submit.prevent="import" class="space-y-4">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-3 pt-4">
                    <x-filament::button type="submit" icon="heroicon-o-upload" class="btn-kominfo-primary">
                        Import Data Penduduk
                    </x-filament::button>

                    <a href="data:text/csv;charset=utf-8,nomor_kk,nik,nama_lengkap,jenis_kelamin,tempat_lahir,tanggal_lahir,agama,status_perkawinan,pendidikan,pekerjaan,penghasilan,nomor_hp%0A7171010101010001,7171010101800004,Contoh%20Penduduk,Laki-laki,Tahuna,1990-01-01,Kristen,Kawin,SMA,Nelayan,2500000,081234567890"
                       download="template-import-penduduk.csv"
                       class="inline-flex items-center justify-center rounded-lg border-2 border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <span class="mr-2">📋</span> Download Template CSV
                    </a>
                </div>
            </form>
        </div>

        <!-- Failure Log -->
        <div class="card-kominfo">
            <div class="card-kominfo-header">
                <h3 class="text-lg font-bold text-white">⚠️ Log Gagal Terbaru</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-slate-100 to-slate-50 dark:from-slate-700 dark:to-slate-600 text-left border-b border-slate-200 dark:border-slate-700">
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Baris</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Nomor KK</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">NIK</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Nama</th>
                            <th class="px-6 py-3 font-semibold text-slate-900 dark:text-white">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($this->recentFailures as $failure)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <td class="px-6 py-3 font-mono text-slate-700 dark:text-slate-300">{{ $failure->baris }}</td>
                                <td class="px-6 py-3 font-mono text-slate-700 dark:text-slate-300">{{ $failure->nomor_kk }}</td>
                                <td class="px-6 py-3 font-mono text-slate-700 dark:text-slate-300">{{ $failure->nik }}</td>
                                <td class="px-6 py-3 font-medium text-slate-900 dark:text-white">{{ $failure->nama }}</td>
                                <td class="px-6 py-3 text-red-600 dark:text-red-400">{{ $failure->alasan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-green-300 dark:text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Belum ada log gagal. Semua data berhasil!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
