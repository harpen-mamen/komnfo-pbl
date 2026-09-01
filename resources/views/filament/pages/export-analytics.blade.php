<x-filament::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 p-8 shadow-2xl">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-72 h-72 bg-white/5 rounded-full blur-3xl"></div>
            <div class="relative z-10">
                
            </div>
        </div>

        <!-- Export Options -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Export Penduduk as CSV -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="bg-gradient-to-r from-blue-500 to-cyan-500 p-6 text-white">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">📊</span>
                        <h2 class="text-xl font-bold">Data Penduduk</h2>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Export lengkap data penduduk yang telah diverifikasi ke format CSV
                    </p>
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-3 rounded">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>Isi:</strong> NIK, Nama, Jenis Kelamin, TTL, Agama, Pendidikan, Pekerjaan, Penghasilan, dan lainnya
                        </p>
                    </div>
                    <form method="POST" action="{{ route('filament.pages.export-analitik.export-penduduk-csv') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white font-semibold rounded-lg transition shadow-md">
                            <span class="flex items-center justify-center gap-2">
                                <span>📥</span>
                                <span>Download CSV</span>
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Export Statistics as JSON -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 p-6 text-white">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">📈</span>
                        <h2 class="text-xl font-bold">Statistik Lengkap</h2>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Export semua statistik analitik ke format JSON untuk analisis lebih lanjut
                    </p>
                    <div class="bg-purple-50 dark:bg-purple-900/20 border-l-4 border-purple-500 p-3 rounded">
                        <p class="text-sm text-purple-800 dark:text-purple-200">
                            <strong>Isi:</strong> Demografi, Pendidikan, Pekerjaan, Perumahan, Agama, Status Perkawinan, Penghasilan
                        </p>
                    </div>
                    <form method="POST" action="{{ route('filament.pages.export-analitik.export-statistics-json') }}" class="inline">
                        @csrf
                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold rounded-lg transition shadow-md">
                            <span class="flex items-center justify-center gap-2">
                                <span>📥</span>
                                <span>Download JSON</span>
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Summary Cards -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">📋 Ringkasan Statistik</h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                <!-- Demographic -->
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-lg p-4 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">👥 Demografi</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-600 dark:text-gray-400">Total Penduduk:</span> <span class="font-bold text-blue-600">{{ number_format($this->analyticsSummary['demographics']['total']) }}</span></p>
                        <p><span class="text-gray-600 dark:text-gray-400">Laki-laki:</span> <span class="font-bold text-blue-600">{{ number_format($this->analyticsSummary['demographics']['male']) }}</span></p>
                        <p><span class="text-gray-600 dark:text-gray-400">Perempuan:</span> <span class="font-bold text-blue-600">{{ number_format($this->analyticsSummary['demographics']['female']) }}</span></p>
                        <p><span class="text-gray-600 dark:text-gray-400">Rata-rata Usia:</span> <span class="font-bold text-blue-600">{{ $this->analyticsSummary['demographics']['avgAge'] }} tahun</span></p>
                    </div>
                </div>

                <!-- Education -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4 border-l-4 border-green-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">🎓 Pendidikan</h3>
                    <div class="space-y-2 text-sm">
                        @foreach($this->analyticsSummary['education'] as $edu => $count)
                            <p><span class="text-gray-600 dark:text-gray-400">{{ $edu }}:</span> <span class="font-bold text-green-600">{{ number_format($count) }}</span></p>
                        @endforeach
                    </div>
                </div>

                <!-- Employment -->
                <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-lg p-4 border-l-4 border-orange-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">💼 Pekerjaan (Top 3)</h3>
                    <div class="space-y-2 text-sm">
                        @foreach($this->analyticsSummary['employment'] as $job => $count)
                            @if($loop->index < 3)
                                <p><span class="text-gray-600 dark:text-gray-400">{{ $job }}:</span> <span class="font-bold text-orange-600">{{ number_format($count) }}</span></p>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Housing -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 border-l-4 border-purple-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">🏠 Perumahan</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-gray-600 dark:text-gray-400">Total Rumah:</span> <span class="font-bold text-purple-600">{{ number_format($this->analyticsSummary['housing']['totalRumah']) }}</span></p>
                        <p><span class="text-gray-600 dark:text-gray-400">Total Keluarga:</span> <span class="font-bold text-purple-600">{{ number_format($this->analyticsSummary['housing']['totalKeluarga']) }}</span></p>
                        <p><span class="text-gray-600 dark:text-gray-400">Rata-rata KK/Rumah:</span> <span class="font-bold text-purple-600">{{ $this->analyticsSummary['housing']['avgKeluargaPerRumah'] }}</span></p>
                    </div>
                </div>

                <!-- Religion -->
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 rounded-lg p-4 border-l-4 border-indigo-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">🕌 Agama</h3>
                    <div class="space-y-2 text-sm">
                        @foreach($this->analyticsSummary['religion'] as $rel => $count)
                            <p><span class="text-gray-600 dark:text-gray-400">{{ $rel }}:</span> <span class="font-bold text-indigo-600">{{ number_format($count) }}</span></p>
                        @endforeach
                    </div>
                </div>

                <!-- Income Distribution -->
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 dark:from-yellow-900/20 dark:to-amber-900/20 rounded-lg p-4 border-l-4 border-yellow-500">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">💰 Penghasilan (Top 3)</h3>
                    <div class="space-y-2 text-sm">
                        @foreach($this->analyticsSummary['incomeDistribution'] as $range => $count)
                            @if($loop->index < 3)
                                <p><span class="text-gray-600 dark:text-gray-400">{{ $range }}:</span> <span class="font-bold text-yellow-600">{{ number_format($count) }}</span></p>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Box -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border-l-4 border-amber-500 rounded-xl p-6">
            <h3 class="text-lg font-bold text-amber-900 dark:text-amber-100 mb-3">⚠️ Informasi Penting</h3>
            <ul class="text-amber-800 dark:text-amber-200 space-y-2 text-sm">
                <li class="flex gap-2">
                    <span>•</span>
                    <span>Data yang di-export hanya mencakup data yang telah diverifikasi dan disetujui</span>
                </li>
                <li class="flex gap-2">
                    <span>•</span>
                    <span>Format CSV dapat dibuka dengan Excel, Google Sheets, atau aplikasi spreadsheet lainnya</span>
                </li>
                <li class="flex gap-2">
                    <span>•</span>
                    <span>Format JSON dapat digunakan untuk analisis lebih lanjut atau integrasi dengan sistem lain</span>
                </li>
                <li class="flex gap-2">
                    <span>•</span>
                    <span>Waktu download tergantung pada jumlah data dan kecepatan koneksi internet</span>
                </li>
            </ul>
        </div>
    </div>
</x-filament::page>
