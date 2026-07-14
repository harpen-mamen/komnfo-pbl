<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="create" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-plus-circle">
                    Tambah Kecamatan
                </x-filament::button>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-4 py-3 font-medium">Daftar Kecamatan</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-4 py-2">Kabupaten</th>
                            <th class="px-4 py-2">Kecamatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->kecamatans as $kecamatan)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $kecamatan->kabupaten->nama }}</td>
                                <td class="px-4 py-2">{{ $kecamatan->nama }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">Belum ada kecamatan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
