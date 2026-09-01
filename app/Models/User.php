<?php

namespace App\Models;

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
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'kecamatan_id',
        'desa_id',
        'opd_id',
        'puskes_id',
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
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function puskes(): BelongsTo
    {
        return $this->belongsTo(FasilitasKesehatan::class, 'puskes_id');
    }

    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }

    // Access Control — SPEC-005: delegate to AppRole enum.
    public function canAccessFilament(): bool
    {
        $roleName = $this->roles->first()?->name;

        if (! $roleName) {
            return false;
        }

        return AppRole::tryFrom($roleName)?->canAccessFilament() ?? false;
    }

    // Role Check Helpers
    public function isAdminKecamatan(): bool
    {
        return $this->hasRole('admin_kecamatan');
    }

    public function isAdminDesa(): bool
    {
        return $this->hasRole('admin_desa');
    }

    public function isOpdAdmin(): bool
    {
        return $this->hasAnyRole([
            'admin_kesehatan',
            'admin_pendidikan',
            'admin_pu',
            'admin_sosial',
            'admin_ekonomi',
            'admin_kependudukan',
            'admin_lingkungan',
            'admin_opd',
            'admin_dinkes',
            'admin_dispend',
            'admin_dpu',
            'admin_dinsos',
            'admin_disperindag',
            'admin_dukcapil',
            'admin_dlh',
            'admin_distan',
            'admin_dishub',
            'admin_distpar',
            'admin_dispora',
            'admin_bkpsdmd',
            'admin_bappeda',
            'admin_bapenda',
            'admin_bpbd',
            'admin_diskominfo',
            'admin_dpmptsp',
            'admin_distaman',
        ]);
    }

    public function isOpdOperator(): bool
    {
        return $this->hasAnyRole([
            'operator_kesehatan',
            'operator_pendidikan',
            'operator_pu',
            'operator_sosial',
            'operator_ekonomi',
            'operator_kependudukan',
            'operator_lingkungan',
            'operator_dinkes',
            'operator_dispend',
            'operator_dpu',
            'operator_dinsos',
            'operator_disperindag',
            'operator_dukcapil',
            'operator_dlh',
            'operator_distan',
            'operator_dishub',
            'operator_distpar',
            'operator_dispora',
            'operator_bkpsdmd',
            'operator_bappeda',
            'operator_bapenda',
            'operator_bpbd',
            'operator_diskominfo',
            'operator_dpmptsp',
            'operator_distaman',
        ]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    public function isKominfoAdmin(): bool
    {
        return $this->hasAnyRole(['admin_kominfo', 'admin']);
    }

    // Get user's OPD module — SPEC-005: delegate to AppRole enum.
    public function getOpdModule(): ?string
    {
        $roleName = $this->roles->first()?->name;

        if (! $roleName) {
            return null;
        }

        return AppRole::tryFrom($roleName)?->opdModule() ?? null;
    }

    // Get role label — SPEC-005: delegate to AppRole enum.
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
