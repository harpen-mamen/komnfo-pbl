<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * DataKendala (FASE 1 - Prinsip 9, 21)
 *
 * Laporan "data tidak ditemukan" dari Admin Desa via mobile.
 *
 * Flow:
 *   Admin Desa tidak menemukan KK/Penduduk di master data
 *     -> Buat DataKendala
 *       -> Diteruskan ke OPD terkait (Dukcapil untuk penduduk, dll)
 *         -> OPD verifikasi dan merespons
 *           -> Status: selesai / ditolak
 */
class DataKendala extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'data_kendalas';

    protected $fillable = [
        'reported_by',
        'desa_id',
        'dusun_id',
        'jenis_kendala',
        'nik',
        'nomor_kk',
        'nama_lengkap',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'latitude',
        'longitude',
        'foto_path',
        'deskripsi',
        'status',
        'diteruskan_ke_opd_id',
        'diteruskan_at',
        'verified_by',
        'verified_at',
        'response_notes',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'diteruskan_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public const KENDALA_PENDUDUK = 'penduduk_tidak_ditemukan';
    public const KENDALA_KK = 'kk_tidak_ditemukan';
    public const KENDALA_DATA_TIDAK_LENGKAP = 'data_tidak_lengkap';
    public const KENDALA_DATA_TIDAK_KONSISTEN = 'data_tidak_konsisten';
    public const KENDALA_FOTO = 'foto_tidak_bisa_diambil';
    public const KENDALA_KOORDINAT = 'koordinat_tidak_terbaca';
    public const KENDALA_LAINNYA = 'lainnya';

    public const STATUS_BARU = 'baru';
    public const STATUS_DITERUSKAN = 'diteruskan';
    public const STATUS_DIVERIFIKASI = 'diverifikasi';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DITOLAK = 'ditolak';

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function diteruskanKeOpd(): BelongsTo
    {
        return $this->belongsTo(Opd::class, 'diteruskan_ke_opd_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
