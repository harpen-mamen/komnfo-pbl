<div>
    <x-filament::page>
        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight">Statistik Kesehatan Otomatis</h2>
            <p class="text-sm text-gray-500">Data dihitung otomatis dari data penduduk. Tidak ada input manual.</p>
        </div>

        @if(empty($statistik))
            <x-filament::card>
                <p class="text-center text-gray-500">Pilih kecamatan dan periode untuk melihat statistik.</p>
            </x-filament::card>
        @else
            <div class="mb-4">
                <span class="text-sm text-gray-500">
                    Periode: {{ $bulan }} {{ $tahun }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 sm:grid-cols-4">
                <x-filament::card>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ $statistik["data"]["ibu_hamil"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Ibu Hamil</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ $statistik["data"]["bayi_lahir"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Bayi Lahir</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $statistik["data"]["balita"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Balita</div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-orange-600">{{ $statistik["data"]["bayi_0_12_bulan"] ?? 0 }}</div>
                        <div class="text-sm text-gray-500">Bayi 0-12 Bulan</div>
                    </div>
                </x-filament::card>
            </div>

            <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
                <x-filament::card>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-500">Anak Pendek (Stunting)</div>
                            <div class="text-2xl font-bold text-red-600">{{ $statistik["data"]["anak_pendek"] ?? 0 }}</div>
                        </div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-500">Ibu Menyusui</div>
                            <div class="text-2xl font-bold text-pink-600">{{ $statistik["data"]["ibu_menyusui"] ?? 0 }}</div>
                        </div>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-500">Lansia (>60 tahun)</div>
                            <div class="text-2xl font-bold text-gray-600">{{ $statistik["data"]["lansia"] ?? 0 }}</div>
                        </div>
                    </div>
                </x-filament::card>
            </div>

            <x-filament::card class="bg-blue-50 border-blue-200">
                <p class="text-sm text-blue-800">
                    <strong>Catatan:</strong> Statistik kesehatan dihitung otomatis dari data penduduk.
                </p>
            </x-filament::card>
        @endif
    </x-filament::page>
</div>
