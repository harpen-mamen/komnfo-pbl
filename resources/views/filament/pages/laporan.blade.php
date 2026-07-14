<x-filament::page>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($reports as $report)
            <a
                href="{{ $report['url'] }}"
                target="_blank"
                class="rounded-lg border border-gray-200 bg-white p-4 text-sm font-medium text-primary-700 shadow-sm hover:bg-gray-50"
            >
                {{ $report['label'] }}
            </a>
        @endforeach
    </div>
</x-filament::page>
