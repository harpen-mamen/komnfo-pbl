<x-filament::page>
    <div class="space-y-4">
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Kecamatan</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Desa</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Rumah</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Keluarga</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">Penduduk</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->rows as $row)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $row['nama'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['desa'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['rumah'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['keluarga'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $row['penduduk'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada data kecamatan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
