<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-lg font-semibold mb-4">Pilih Dataset</h2>
        <p class="text-sm text-gray-600 mb-4">
            Pilih dataset sesuai bidang tugas OPD Anda. Dataset adalah kumpulan data
            yang dikumpulkan oleh satu OPD sesuai tupoksinya.
        </p>

        @foreach ($this->getDatasets() as $opdName => $datasets)
            <div class="mb-4">
                <h3 class="text-md font-medium text-gray-800 border-b pb-1 mb-2">
                    {{ $opdName }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($datasets as $dataset)
                        <a href="{{ route('filament.admin.resources.dataset-values.index', ['dataset' => $dataset->kode]) }}"
                           class="block p-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-primary-500 transition">
                            <div class="font-semibold text-sm text-gray-900">
                                {{ $dataset->nama }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <code class="bg-gray-100 px-1 rounded">{{ $dataset->kode }}</code>
                                • {{ $dataset->period_type }}
                                • {{ $dataset->fields->count() }} field
                            </div>
                            @if ($dataset->deskripsi)
                                <p class="text-xs text-gray-600 mt-2 line-clamp-2">{{ $dataset->deskripsi }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </x-filament::section>
</x-filament-widgets::widget>
