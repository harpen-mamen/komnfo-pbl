@php
    $user = Filament\Facades\Filament::auth()->user();
    $avatarUrl = $user?->getFilamentAvatarUrl();
    $initials = $user?->initials ?? 'U';
    $roleLabel = $user?->role_label ?? '-';
@endphp

<div class="filament-sidebar-footer border-t border-white/10 px-4 py-4">
    <div class="flex items-center gap-3">
        @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-11 w-11 shrink-0 rounded-full border-2 border-white/25 object-cover shadow-lg" />
        @else
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2 border-white/25 bg-gradient-to-br from-[#0d9488] to-[#06b6d4] text-sm font-bold text-white shadow-lg">
                {{ $initials }}
            </div>
        @endif
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-white">{{ $user?->name ?? 'User' }}</p>
            <p class="truncate text-xs text-white/50">{{ $roleLabel }}</p>
        </div>
        <a
            href="{{ url('/admin/pengaturan-profil') }}"
            title="Edit Profil"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white/10 text-white/60 transition-all hover:bg-[#0d9488] hover:text-white hover:shadow-lg"
        >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
        </a>
    </div>
</div>