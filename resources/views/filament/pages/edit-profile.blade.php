<x-filament::page>
<style>
.profile-page { font-family: "Inter", sans-serif; max-width: 960px; margin: 0 auto; padding: 1.5rem; color: #e2e8f0; }
.profile-header { display: grid; grid-template-columns: auto 1fr; gap: 2rem; align-items: start; background: linear-gradient(135deg, #060f1d 0%, #0a1628 40%, #0d4a47 100%); border-radius: 24px; padding: 2.5rem; color: white; position: relative; overflow: hidden; margin-bottom: 2rem; border: 1px solid rgba(13,148,136,0.2); box-shadow: 0 8px 40px rgba(0,0,0,0.5); }
.profile-header::before { content: ""; position: absolute; top: -100px; right: -100px; width: 350px; height: 350px; border-radius: 50%; background: rgba(13,148,136,0.1); pointer-events: none; }
.profile-header::after { content: ""; position: absolute; bottom: -80px; right: 60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(6,182,212,0.08); pointer-events: none; }
.profile-avatar-wrap { position: relative; width: 120px; height: 120px; }
.profile-avatar { width: 120px; height: 120px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); object-fit: cover; box-shadow: 0 8px 32px rgba(0,0,0,0.4); }
.profile-avatar-fallback { width: 120px; height: 120px; border-radius: 50%; border: 4px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; color: white; background: linear-gradient(135deg, rgba(13,148,136,0.6), rgba(6,182,212,0.4)); }
.profile-header-content { display: flex; flex-direction: column; gap: 0.75rem; position: relative; z-index: 1; }
.profile-name { font-size: 1.75rem; font-weight: 800; color: white; letter-spacing: -0.02em; line-height: 1.2; }
.profile-role { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.85rem; background: rgba(13,148,136,0.25); border: 1px solid rgba(13,148,136,0.4); border-radius: 999px; font-size: 0.8rem; font-weight: 700; color: #5eead4; }
.profile-meta { display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 0.875rem; color: rgba(255,255,255,0.65); }
.profile-meta-item { display: flex; align-items: center; gap: 0.4rem; }
.profile-grid { display: grid; grid-template-columns: 340px 1fr; gap: 2rem; align-items: start; }
.profile-sidebar { display: flex; flex-direction: column; gap: 1.25rem; }
.profile-info-card { background: #0f1d2e; border: 1px solid rgba(148,163,184,0.12); border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,0.35); }
.profile-info-card h3 { font-size: 0.95rem; font-weight: 800; color: #e2e8f0; margin: 0 0 1.25rem 0; }
.profile-info-row { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 0; border-bottom: 1px solid rgba(148,163,184,0.06); gap: 1rem; }
.profile-info-row:last-child { border-bottom: none; }
.profile-info-label { font-size: 0.82rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
.profile-info-value { font-size: 0.9rem; color: #e2e8f0; font-weight: 600; text-align: right; max-width: 200px; word-break: break-word; }
.profile-form-card { background: #0f1d2e; border: 1px solid rgba(148,163,184,0.12); border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.35); }
.profile-form-title { font-size: 1.1rem; font-weight: 800; color: #e2e8f0; margin: 0 0 0.25rem 0; }
.profile-form-subtitle { font-size: 0.875rem; color: #94a3b8; margin: 0 0 1.75rem 0; }
.profile-form-group { margin-bottom: 1.5rem; }
.profile-form-label { display: block; font-size: 0.82rem; font-weight: 700; color: #e2e8f0; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.04em; }
.profile-form-input { width: 100%; padding: 0.85rem 1rem; border: 2px solid rgba(148,163,184,0.22); border-radius: 12px; font-size: 0.9375rem; color: #e2e8f0; background: #111d32; outline: none; box-sizing: border-box; transition: border-color 0.2s, box-shadow 0.2s; }
.profile-form-input:focus { border-color: #0d9488; box-shadow: 0 0 0 4px rgba(13,148,136,0.25); }
.profile-form-hint { font-size: 0.78rem; color: #94a3b8; margin-top: 0.4rem; }
.profile-avatar-upload { margin-bottom: 1.5rem; }
.profile-avatar-upload-area { border: 2px dashed rgba(148,163,184,0.22); border-radius: 16px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s; }
.profile-avatar-upload-area:hover { border-color: #0d9488; background: rgba(13,148,136,0.08); }
.profile-avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 0.75rem; border: 3px solid rgba(148,163,184,0.22); }
.profile-avatar-icon { width: 80px; height: 80px; border-radius: 50%; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #0d9488, #06b6d4); color: white; font-size: 1.75rem; font-weight: 800; }
.profile-form-actions { display: flex; gap: 0.75rem; margin-top: 2rem; justify-content: flex-end; }
.btn-save { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.85rem 1.75rem; background: linear-gradient(135deg, #0d9488, #14b8a6); color: white; border: none; border-radius: 12px; font-size: 0.9375rem; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(13,148,136,0.35); transition: all 0.2s ease; }
.btn-save:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(13,148,136,0.5); }
.section-divider { height: 1px; background: linear-gradient(to right, transparent, rgba(148,163,184,0.12), transparent); margin: 1.5rem 0; }
.section-title { font-size: 0.78rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin: 1.5rem 0 1rem 0; }
.alert-success { background: rgba(16,185,129,0.1); color: #34d399; border: 1px solid rgba(16,185,129,0.25); padding: 0.875rem 1rem; border-radius: 12px; font-weight: 600; margin-bottom: 1rem; }
.alert-error { background: rgba(239,68,68,0.1); color: #f87171; border: 1px solid rgba(239,68,68,0.25); padding: 0.875rem 1rem; border-radius: 12px; font-weight: 600; margin-bottom: 1rem; }
@media (max-width: 768px) { .profile-grid { grid-template-columns: 1fr; } .profile-header { grid-template-columns: 1fr; text-align: center; } }
</style>

<div class="profile-page">
    <div class="profile-header">
        <div class="profile-avatar-wrap">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Avatar" class="profile-avatar" />
            @else
                <div class="profile-avatar-fallback">{{ $initials }}</div>
            @endif
        </div>
        <div class="profile-header-content">
            <h1 class="profile-name">{{ $user->name }}</h1>
            <span class="profile-role">{{ $user->role_label }}</span>
            <div class="profile-meta">
                <span class="profile-meta-item">{{ $user->email }}</span>
                @if($user->nip)
                    <span class="profile-meta-item">NIP: {{ $user->nip }}</span>
                @endif
                @if($user->jabatan)
                    <span class="profile-meta-item">{{ $user->jabatan }}</span>
                @endif
            </div>
        </div>
    </div>

    <div class="profile-grid">
        <aside class="profile-sidebar">
            <div class="profile-info-card">
                <h3>Informasi Akun</h3>
                <div class="profile-info-row">
                    <span class="profile-info-label">Nama</span>
                    <span class="profile-info-value">{{ $user->name }}</span>
                </div>
                <div class="profile-info-row">
                    <span class="profile-info-label">Email</span>
                    <span class="profile-info-value">{{ $user->email }}</span>
                </div>
                @if($user->nip)
                <div class="profile-info-row">
                    <span class="profile-info-label">NIP</span>
                    <span class="profile-info-value">{{ $user->nip }}</span>
                </div>
                @endif
                @if($user->jabatan)
                <div class="profile-info-row">
                    <span class="profile-info-label">Jabatan</span>
                    <span class="profile-info-value">{{ $user->jabatan }}</span>
                </div>
                @endif
                @if($user->no_hp)
                <div class="profile-info-row">
                    <span class="profile-info-label">No. HP</span>
                    <span class="profile-info-value">{{ $user->no_hp }}</span>
                </div>
                @endif
                <div class="profile-info-row">
                    <span class="profile-info-label">Role</span>
                    <span class="profile-info-value">{{ $user->role_label }}</span>
                </div>
                @if($user->kecamatan)
                <div class="profile-info-row">
                    <span class="profile-info-label">Kecamatan</span>
                    <span class="profile-info-value">{{ $user->kecamatan->nama }}</span>
                </div>
                @endif
                @if($user->desa)
                <div class="profile-info-row">
                    <span class="profile-info-label">Desa</span>
                    <span class="profile-info-value">{{ $user->desa->nama }}</span>
                </div>
                @endif
                @if($user->puskes)
                <div class="profile-info-row">
                    <span class="profile-info-label">Puskes</span>
                    <span class="profile-info-value">{{ $user->puskes->nama }}</span>
                </div>
                @endif
                <div class="profile-info-row">
                    <span class="profile-info-label">Bergabung</span>
                    <span class="profile-info-value">{{ $user->created_at->translatedFormat("d M Y") }}</span>
                </div>
            </div>
        </aside>

        <main>
            <form wire:submit="save" class="profile-form-card">
                <h2 class="profile-form-title">Edit Profil</h2>
                <p class="profile-form-subtitle">Perbarui informasi profil dan foto Anda di bawah ini.</p>

                <div class="profile-avatar-upload">
                    <label class="profile-avatar-upload-area">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="profile-avatar-preview" />
                        @else
                            <div class="profile-avatar-icon">{{ $initials }}</div>
                        @endif
                        <div style="font-size:0.85rem;color:#94a3b8;font-weight:600;">Klik untuk upload foto profil</div>
                        <div style="font-size:0.75rem;color:#64748b;margin-top:0.25rem;">JPG, PNG maks. 2MB</div>
                        <input type="file" wire:model="foto" accept="image/*" style="display:none" />
                    </label>
                </div>

                <div class="section-title">Informasi Dasar</div>

                <div class="profile-form-group">
                    <label class="profile-form-label">Nama Lengkap</label>
                    <input type="text" wire:model="name" class="profile-form-input" placeholder="Masukkan nama lengkap" required />
                    @error("name")
                        <div style="font-size:0.78rem;color:#f87171;margin-top:0.4rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="profile-form-group">
                    <label class="profile-form-label">Alamat Email</label>
                    <input type="email" wire:model="email" class="profile-form-input" placeholder="email@contoh.com" required />
                    @error("email")
                        <div style="font-size:0.78rem;color:#f87171;margin-top:0.4rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="section-divider"></div>
                <div class="section-title">Ubah Password</div>
                <p style="font-size:0.82rem;color:#94a3b8;margin:-0.75rem 0 1.25rem 0;">Kosongkan jika tidak ingin mengubah password.</p>

                <div class="profile-form-group">
                    <label class="profile-form-label">Password Saat Ini</label>
                    <input type="password" wire:model="current_password" class="profile-form-input" placeholder="Masukkan password saat ini" autocomplete="current-password" />
                    @error("current_password")
                        <div style="font-size:0.78rem;color:#f87171;margin-top:0.4rem;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="profile-form-group">
                        <label class="profile-form-label">Password Baru</label>
                        <input type="password" wire:model="password" class="profile-form-input" placeholder="Min. 8 karakter" autocomplete="new-password" />
                        @error("password")
                            <div style="font-size:0.78rem;color:#f87171;margin-top:0.4rem;">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="profile-form-group">
                        <label class="profile-form-label">Konfirmasi Password</label>
                        <input type="password" wire:model="password_confirmation" class="profile-form-input" placeholder="Ulangi password baru" autocomplete="new-password" />
                    </div>
                </div>

                <div class="profile-form-actions">
                    <button type="submit" class="btn-save">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>
</x-filament::page>