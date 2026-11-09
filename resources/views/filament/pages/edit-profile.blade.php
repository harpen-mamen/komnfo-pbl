{{-- SIPANDU Edit Profile - Bersih, Tegas, Elegan --}}
<x-filament-panels::page>
<div class="ds-container">
    <header class="ds-hero ds-hero--profile">
        <div class="ds-hero__bg"></div>
        <div class="ds-hero__content" style="display: flex; align-items: center; gap: 1.5rem;">
            <div class="ds-avatar ds-avatar--lg ds-avatar--profile" style="width: 80px; height: 80px; font-size: 1.5rem; background: rgba(255,255,255,0.2); color: white; border: 3px solid rgba(255,255,255,0.3);">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" />
                @else
                    {{ $initials }}
                @endif
            </div>
            <div>
                <p class="ds-eyebrow" style="color: rgba(255,255,255,0.55);">PROFIL PENGGUNA</p>
                <h1 class="ds-hero__title" style="margin: 0.25rem 0 0; font-size: 1.75rem;">{{ $user->name }}</h1>
                <p class="ds-hero__subtitle" style="margin: 0.25rem 0 0; font-size: 0.875rem; color: rgba(255,255,255,0.7);">
                    {{ $user->email }} · {{ $user->role_label }}
                </p>
            </div>
        </div>
    </header>

    <div class="ds-profile-grid">
        <aside>
            <article class="ds-card">
                <div class="ds-card-header">
                    <h2 class="ds-heading ds-heading--sm">Informasi Akun</h2>
                </div>
                <div class="ds-card-body ds-stack ds-stack--2">
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Nama</span>
                        <span class="ds-info-row__value">{{ $user->name }}</span>
                    </div>
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Email</span>
                        <span class="ds-info-row__value">{{ $user->email }}</span>
                    </div>
                    @if($user->nip)
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">NIP</span>
                        <span class="ds-info-row__value">{{ $user->nip }}</span>
                    </div>
                    @endif
                    @if($user->jabatan)
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Jabatan</span>
                        <span class="ds-info-row__value">{{ $user->jabatan }}</span>
                    </div>
                    @endif
                    @if($user->no_hp)
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">No. HP</span>
                        <span class="ds-info-row__value">{{ $user->no_hp }}</span>
                    </div>
                    @endif
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Role</span>
                        <span class="ds-info-row__value">
                            <span class="ds-badge ds-badge--primary">{{ $user->role_label }}</span>
                        </span>
                    </div>
                    @if($user->kecamatan)
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Kecamatan</span>
                        <span class="ds-info-row__value">{{ $user->kecamatan->nama }}</span>
                    </div>
                    @endif
                    @if($user->desa)
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Desa</span>
                        <span class="ds-info-row__value">{{ $user->desa->nama }}</span>
                    </div>
                    @endif
                    <div class="ds-info-row">
                        <span class="ds-info-row__label">Bergabung</span>
                        <span class="ds-info-row__value">{{ $user->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </article>
        </aside>

        <main>
            <form wire:submit="save" class="ds-card">
                <div class="ds-card-header">
                    <h2 class="ds-heading ds-heading--sm">Edit Profil</h2>
                    <p class="ds-text ds-text--muted ds-mt-2 ds-text--sm">Perbarui informasi profil Anda di bawah ini.</p>
                </div>
                <div class="ds-card-body ds-stack ds-stack--6">
                    <div>
                        <label class="ds-label">Foto Profil</label>
                        <label class="ds-upload-area">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="Avatar" class="ds-upload-area__preview" />
                            @else
                                <div class="ds-upload-area__placeholder">{{ $initials }}</div>
                            @endif
                            <p class="ds-text ds-text--muted ds-mt-2 ds-text--sm">Klik untuk upload foto baru</p>
                            <p class="ds-text ds-text--subtle ds-text--sm">JPG, PNG · Maks. 2MB</p>
                            <input type="file" wire:model="foto" accept="image/*" class="ds-upload-area__input" />
                        </label>
                    </div>

                    <div>
                        <label class="ds-label">Nama Lengkap</label>
                        <input type="text" wire:model="name" class="ds-input" placeholder="Masukkan nama lengkap" required />
                        @error("name") <p class="ds-text ds-text--sm" style="color: var(--ds-danger-600); margin-top: 0.5rem;">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ds-label">Alamat Email</label>
                        <input type="email" wire:model="email" class="ds-input" placeholder="email@contoh.com" required />
                        @error("email") <p class="ds-text ds-text--sm" style="color: var(--ds-danger-600); margin-top: 0.5rem;">{{ $message }}</p> @enderror
                    </div>

                    <hr class="ds-divider" style="margin: 0;" />

                    <div>
                        <h3 class="ds-heading ds-heading--sm ds-mb-2">Ubah Password</h3>
                        <p class="ds-text ds-text--muted ds-text--sm ds-mb-6">Kosongkan jika tidak ingin mengubah password.</p>
                        <div class="ds-stack ds-stack--4">
                            <div>
                                <label class="ds-label">Password Saat Ini</label>
                                <input type="password" wire:model="current_password" class="ds-input" placeholder="Masukkan password saat ini" autocomplete="current-password" />
                            </div>
                            <div class="ds-grid" style="grid-template-columns: 1fr 1fr;">
                                <div>
                                    <label class="ds-label">Password Baru</label>
                                    <input type="password" wire:model="password" class="ds-input" placeholder="Min. 8 karakter" autocomplete="new-password" />
                                </div>
                                <div>
                                    <label class="ds-label">Konfirmasi</label>
                                    <input type="password" wire:model="password_confirmation" class="ds-input" placeholder="Ulangi password" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ds-card-footer ds-row ds-row--end">
                    <button type="submit" class="ds-btn ds-btn--primary">
                        <x-heroicon-o-check class="w-4 h-4" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
</x-filament-panels::page>

<style>
.ds-profile-grid {
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: var(--ds-space-6);
    align-items: start;
    margin-top: var(--ds-space-6);
}

@media (max-width: 900px) {
    .ds-profile-grid { grid-template-columns: 1fr; }
}

.ds-hero--profile {
    margin-top: var(--ds-space-6);
    min-height: 0;
    padding: var(--ds-space-6) var(--ds-space-8);
}

.ds-info-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--ds-space-3);
    padding: 0.625rem 0;
    border-bottom: 1px solid var(--ds-ink-100);
    font-size: 0.875rem;
}

.ds-info-row:last-child { border-bottom: 0; }

.ds-info-row__label {
    color: var(--ds-ink-500);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-size: 0.75rem;
}

.ds-info-row__value {
    color: var(--ds-ink-900);
    font-weight: 600;
    text-align: right;
}

.ds-upload-area {
    display: block;
    border: 2px dashed var(--ds-ink-300);
    border-radius: var(--ds-radius);
    padding: var(--ds-space-6);
    text-align: center;
    cursor: pointer;
    transition: all var(--ds-transition-fast);
    background: var(--ds-ink-50);
}

.ds-upload-area:hover {
    border-color: var(--ds-primary-500);
    background: var(--ds-primary-50);
}

.ds-upload-area__preview {
    width: 80px;
    height: 80px;
    border-radius: 9999px;
    object-fit: cover;
    margin: 0 auto;
    border: 3px solid var(--ds-ink-200);
}

.ds-upload-area__placeholder {
    width: 80px;
    height: 80px;
    border-radius: 9999px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--ds-primary-600), var(--ds-info-600));
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
}

.ds-upload-area__input { display: none; }
</style>
