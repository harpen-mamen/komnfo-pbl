<?php

namespace App\Models;

use App\Enums\AppRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRegistration extends Model
{
    use HasFactory;

    protected $table = 'user_registrations';

    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'no_hp',
        'jabatan',
        'kecamatan_id',
        'desa_id',
        'opd_id',
        'requested_role',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
        'approved_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REVISION = 'revision';

    // Available roles — SPEC-005: delegate to AppRole enum (single source of truth).
    public const AVAILABLE_ROLES = [
        'admin_kecamatan' => 'Admin Kecamatan',
        'admin_desa' => 'Admin Desa',
        'admin_dinkes' => 'Admin Dinkes',
        'operator_dinkes' => 'Operator Dinkes',
        'admin_dispend' => 'Admin Dispend',
        'operator_dispend' => 'Operator Dispend',
        'admin_dpu' => 'Admin DPU',
        'operator_dpu' => 'Operator DPU',
        'admin_dinsos' => 'Admin Dinsos',
        'operator_dinsos' => 'Operator Dinsos',
        'admin_disperindag' => 'Admin Disperindag',
        'operator_disperindag' => 'Operator Disperindag',
        'admin_dukcapil' => 'Admin Dukcapil',
        'operator_dukcapil' => 'Operator Dukcapil',
        'admin_dlh' => 'Admin DLH',
        'operator_dlh' => 'Operator DLH',
        'admin_distan' => 'Admin Distan',
        'operator_distan' => 'Operator Distan',
        'admin_dishub' => 'Admin Dishub',
        'operator_dishub' => 'Operator Dishub',
        'admin_distpar' => 'Admin Distpar',
        'operator_distpar' => 'Operator Distpar',
        'admin_dispora' => 'Admin Dispora',
        'operator_dispora' => 'Operator Dispora',
        'admin_bkpsdmd' => 'Admin BKPSDMD',
        'operator_bkpsdmd' => 'Operator BKPSDMD',
        'admin_bappeda' => 'Admin Bappeda',
        'operator_bappeda' => 'Operator Bappeda',
        'admin_bapenda' => 'Admin Bapenda',
        'operator_bapenda' => 'Operator Bapenda',
        'admin_bpbd' => 'Admin BPBD',
        'operator_bpbd' => 'Operator BPBD',
        'admin_diskominfo' => 'Admin Diskominfo',
        'operator_diskominfo' => 'Operator Diskominfo',
        'admin_dpmptsp' => 'Admin DPMTSP',
        'operator_dpmptsp' => 'Operator DPMTSP',
        'admin_distaman' => 'Admin Distaman',
        'operator_distaman' => 'Operator Distaman',
    ];

    /**
     * SPEC-005: Preferred way to get available roles — delegates to AppRole enum.
     * Use this instead of the legacy AVAILABLE_ROLES const.
     *
     * @return array<string, string>
     */
    public static function getAvailableRoles(): array
    {
        return AppRole::availableForRegistration();
    }

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

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Status label
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Verifikasi',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_REVISION => 'Perlu Revisi',
            default => ucfirst($this->status ?? ''),
        };
    }

    // Status color
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_REVISION => 'info',
            default => 'gray',
        };
    }

    // Scope pending
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope approved
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
}
