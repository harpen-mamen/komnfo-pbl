<div class="filament-widget rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="space-y-4 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Verifikasi Registrasi</h2>
                <p class="text-sm text-gray-500">{{ $pendingCount }} registrasi menunggu pemeriksaan Kominfo.</p>
            </div>
            <a href="{{ \App\Filament\Resources\UserRegistrationResource::getUrl('index') }}"
               class="rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white">
                Buka daftar
            </a>
        </div>

        @if ($registrations->isEmpty())
            <p class="text-sm text-gray-500">Belum ada registrasi yang perlu ditampilkan.</p>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($registrations as $registration)
                    <a class="flex items-center justify-between gap-4 py-3" href="{{ \App\Filament\Resources\UserRegistrationResource::getUrl('edit', ['record' => $registration]) }}">
                        <div>
                            <p class="font-medium">{{ $registration->name }}</p>
                            <p class="text-sm text-gray-500">{{ $registration->email }} · {{ $registration->requested_role }}</p>
                        </div>
                        <span class="rounded-full bg-warning-100 px-2 py-1 text-xs text-warning-700">{{ $registration->status_label }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
