<?php

namespace App\Models;

use Filament\Panel;
use App\Enums\AppRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'device_code',
        'device_code_last_used_at',
        'email',
        'password',
        'kecamatan_id',
        'desa_id',
        'opd_id',
        'nip',
        'jabatan',
        'no_hp',
        'alamat',
        'foto',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relations
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kecamatan_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'desa_id');
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }

    // Access Control — delegate to AppRole enum.
    public function canAccessPanel(Panel $panel): bool
    {
        // Admin Bupati enters the panel through a scoped, read-only dashboard route.
        if ($this->hasRole(AppRole::ADMIN_BUPATI->value)) {
            return true;
        }

        return $this->canAccessFilament();
    }

    // Existing policies and provisioning commands share this access decision.
    public function canAccessFilament(): bool
    {
        // Do not grant Bupati the broad resource access implied by panel login.
        if ($this->hasRole(AppRole::ADMIN_BUPATI->value)) {
            return false;
        }

        $roleName = $this->roles->first()?->name;

        if (! $roleName) {
            return false;
        }

        return AppRole::tryFrom($roleName)?->canAccessFilament() ?? false;
    }

    // Role Check Helpers
    public function isAdminKecamatan(): bool
    {
        return $this->hasRole(AppRole::ADMIN_KECAMATAN->value);
    }

    public function isAdminDesa(): bool
    {
        return $this->hasRole(AppRole::ADMIN_DESA->value);
    }

    public function isAdminLapangan(): bool
    {
        return $this->hasRole(AppRole::ADMIN_LAPANGAN->value);
    }

    public function isWalidata(): bool
    {
        return $this->hasAnyRole([
            AppRole::WALIDATA->value,
            AppRole::ADMIN_KOMINFO->value,
            AppRole::ADMIN->value,
        ]);
    }

    public function isOpdAdmin(): bool
    {
        $roleName = $this->roles->first()?->name;
        $enum = AppRole::tryFrom($roleName ?? '');

        return $enum?->isOpdLevel() ?? false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAnyRole([
            AppRole::SUPER_ADMIN->value,
            AppRole::ADMIN->value,
        ]);
    }

    public function isAdminBerita(): bool
    {
        return $this->hasRole(AppRole::ADMIN_BERITA->value);
    }

    public function isKepalaDinas(): bool
    {
        return $this->hasRole(AppRole::KEPALA_DINAS->value);
    }

    public function isKepalaDaerah(): bool
    {
        return $this->hasAnyRole([
            AppRole::ADMIN_BUPATI->value,
            AppRole::ADMIN_WAKIL_BUPATI->value,
        ]);
    }

    // Get role label — delegate to AppRole enum.
    public function getRoleLabelAttribute(): string
    {
        $role = $this->roles->first();
        if (! $role) {
            return '-';
        }

        return AppRole::tryFrom($role->name)?->label() ?? ucwords(str_replace('_', ' ', $role->name));
    }

    // Avatar — return URL to uploaded profile photo.
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->foto && Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }

        return null;
    }

    // Initials for avatar fallback.
    public function getInitialsAttribute(): string
    {
        $parts = array_filter(explode(' ', trim($this->name ?? '')));

        if (count($parts) >= 2) {
            $first = substr(reset($parts), 0, 1);
            $last = substr(end($parts), 0, 1);

            return strtoupper($first . $last);
        }

        return strtoupper(substr($this->name ?? 'U', 0, 2));
    }

    // Consistent avatar color based on user ID.
    public function getAvatarColorAttribute(): string
    {
        $colors = [
            'bg-blue-500', 'bg-indigo-500', 'bg-violet-500',
            'bg-purple-500', 'bg-pink-500', 'bg-rose-500',
            'bg-red-500', 'bg-orange-500', 'bg-amber-500',
            'bg-emerald-500', 'bg-teal-500', 'bg-cyan-500',
        ];

        return $colors[$this->id % count($colors)];
    }
}
