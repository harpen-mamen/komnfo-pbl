<div>
    <x-filament-panels::page>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight">Statistik Kesehatan Otomatis</h2>
            <p class="text-sm text-gray-500">Ringkasan hanya memakai laporan OPD yang telah diverifikasi dan dipublikasikan Kominfo.</p>
        </div>

        @if(empty($statistik))
            <x-filament::section>
                <p class="text-center text-gray-500">Pilih kecamatan dan periode untuk melihat statistik.</p>
            </x-filament::section>
        @else
            <div class="mb-4">
                <span class="text-sm text-gray-500">
                    Periode: {{ $bulan }} {{ $tahun }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $statistik["data"]["ibu_hamil"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Ibu Hamil</div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $statistik["data"]["bayi_lahir"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Bayi Lahir</div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $statistik["data"]["balita"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Balita</div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ $statistik["data"]["bayi_0_12_bulan"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Bayi 0-12 Bulan</div>
                    </div>
                </x-filament::section>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
                <x-filament::section>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-500">Anak Pendek (Stunting)</div>
                            <div class="text-2xl font-bold text-red-600">{{ $statistik["data"]["anak_pendek"] ?? 0 }}</div>
                        </div>
                    </div>
                </x-filament::section>

                <x-filament::section>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-500">Tenaga Kesehatan</div>
                            <div class="text-2xl font-bold text-pink-600">{{ $statistik["data"]["tenaga_kesehatan"] ?? 0 }}</div>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <x-filament::section class="mb-6">
                <div class="text-sm text-gray-500">Laporan Terverifikasi</div>
                <div class="text-2xl font-bold text-gray-700">{{ $statistik["laporan_terverifikasi"] ?? 0 }}</div>
            </x-filament::section>

            <x-filament::section class="bg-blue-50 border-blue-200">
                <p class="text-sm text-blue-800">
                    <strong>Catatan:</strong> Data yang belum mendapat persetujuan Kominfo tidak dihitung pada infografis ini.
                </p>
            </x-filament::section>
        @endif
    </x-filament-panels::page>
</div>
