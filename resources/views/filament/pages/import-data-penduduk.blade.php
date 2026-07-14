<x-filament::page>
    <form wire:submit.prevent="import" class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button type="submit" icon="heroicon-o-upload">
                Import Data Penduduk
            </x-filament::button>

            <a href="data:text/csv;charset=utf-8,nomor_kk,nik,nama_lengkap,jenis_kelamin,tempat_lahir,tanggal_lahir,agama,status_perkawinan,pendidikan,pekerjaan,penghasilan,nomor_hp%0A7171010101010001,7171010101800004,Contoh%20Penduduk,Laki-laki,Tahuna,1990-01-01,Kristen,Kawin,SMA,Nelayan,2500000,081234567890"
               download="template-import-penduduk.csv"
               class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Download Template CSV
            </a>
        </div>
    </form>

    <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white">
        <div class="border-b border-gray-200 px-4 py-3 font-medium">Log Gagal Terbaru</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-2">Baris</th>
                        <th class="px-4 py-2">Nomor KK</th>
                        <th class="px-4 py-2">NIK</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($this->recentFailures as $failure)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $failure->baris }}</td>
                            <td class="px-4 py-2">{{ $failure->nomor_kk }}</td>
                            <td class="px-4 py-2">{{ $failure->nik }}</td>
                            <td class="px-4 py-2">{{ $failure->nama }}</td>
                            <td class="px-4 py-2">{{ $failure->alasan }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada log gagal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
