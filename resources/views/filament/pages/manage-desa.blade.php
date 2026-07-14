<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="create" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap items-center gap-3">
                <x-filament::button type="submit" icon="heroicon-o-plus-circle">
                    Tambah Desa
                </x-filament::button>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-4 py-3 font-medium">Daftar Desa</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-4 py-2">Kecamatan</th>
                            <th class="px-4 py-2">Desa</th>
                            <th class="px-4 py-2">Kode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->desas as $desa)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $desa->kecamatan->nama }}</td>
                                <td class="px-4 py-2">{{ $desa->nama }}</td>
                                <td class="px-4 py-2">{{ $desa->kode }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada desa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament::page>
