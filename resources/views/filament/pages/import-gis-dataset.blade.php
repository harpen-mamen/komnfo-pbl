<x-filament-panels::page>
    <div class="mx-auto max-w-5xl space-y-5">
        <div class="rounded-xl border border-primary-200 bg-primary-50 p-5 text-sm leading-6 text-gray-700">
            <h2 class="font-semibold text-gray-900">Impor objek ke {{ $dataset->nama }}</h2>
            <p class="mt-1">Setiap geometri menjadi satu baris data. Objek yang diimpor berstatus draft; sistem tidak menerbitkannya otomatis.</p>
        </div>
        {{ $this->form }}
        <div class="flex flex-wrap justify-end gap-3">
            <a href="{{ \App\Filament\Resources\DynamicDatasetResource::getUrl('index', ['dataset' => $dataset->kode]) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold">Batal</a>
            <x-filament::button wire:click="import" icon="heroicon-o-arrow-up-tray">Validasi dan impor sebagai draft</x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
