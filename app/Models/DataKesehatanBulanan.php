<?php

namespace App\Models;

use App\Models\Concerns\HasSubmissionBatchApproval;
use App\Support\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DataKesehatanBulanan extends Model
{
    use HasFactory;
    use HasSubmissionBatchApproval;

    public const STATUS = [
        'Terkirim' => 'Terkirim',
        'Terverifikasi' => 'Terverifikasi',
        'Perlu Revisi' => 'Perlu Revisi',
    ];

    protected $table = 'data_kesehatan_bulanans';

    protected $fillable = [
        'periode_bulan',
        'periode_tahun',
        'periode_pelaporan_id',
        'puskesmas',
        'kecamatan_id',
        'submission_batch_id',
        'desa_id',
        'jumlah_balita',
        'jumlah_ibu_hamil',
        'stunting',
        'gizi_buruk',
        'kasus_penyakit',
        'jenis', 'kunjungan', 'rawat_jalan', 'rawat_inap', 'rujukan', 'persalinan', 'ibu_hamil', 'balita', 'bayi', 'imunisasi',
        'ispa', 'dbd', 'malaria', 'tbc', 'hipertensi', 'diabetes', 'covid', 'lainnya', 'dokter', 'perawat', 'bidan', 'apoteker', 'ahli_gizi',
        'ambulans', 'laboratorium', 'apotek', 'ruang_bersalin', 'internet', 'listrik', 'air_bersih', 'status_obat', 'permasalahan', 'uraian_permasalahan',
        'disabilitas',
        'detail_kia', 'detail_tenaga_kesehatan', 'detail_pelayanan',
        'kematian',
        'kelahiran',
        'bpjs_aktif',
        'bpjs_tidak_aktif',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'periode_bulan' => 'integer',
        'periode_tahun' => 'integer',
        'jumlah_balita' => 'integer',
        'jumlah_ibu_hamil' => 'integer',
        'stunting' => 'integer',
        'gizi_buruk' => 'integer',
        'kasus_penyakit' => 'array',
        'kunjungan' => 'integer', 'rawat_jalan' => 'integer', 'rawat_inap' => 'integer', 'rujukan' => 'integer', 'persalinan' => 'integer', 'ibu_hamil' => 'integer', 'balita' => 'integer', 'bayi' => 'integer', 'imunisasi' => 'integer',
        'ispa' => 'integer', 'dbd' => 'integer', 'malaria' => 'integer', 'tbc' => 'integer', 'hipertensi' => 'integer', 'diabetes' => 'integer', 'covid' => 'integer', 'lainnya' => 'integer', 'dokter' => 'integer', 'perawat' => 'integer', 'bidan' => 'integer', 'apoteker' => 'integer', 'ahli_gizi' => 'integer',
        'ambulans' => 'boolean', 'laboratorium' => 'boolean', 'apotek' => 'boolean', 'ruang_bersalin' => 'boolean', 'internet' => 'boolean', 'listrik' => 'boolean', 'air_bersih' => 'boolean', 'permasalahan' => 'array',
        'disabilitas' => 'integer',
        'detail_kia' => 'array', 'detail_tenaga_kesehatan' => 'array', 'detail_pelayanan' => 'array',
        'kematian' => 'integer',
        'kelahiran' => 'integer',
        'bpjs_aktif' => 'integer',
        'bpjs_tidak_aktif' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (DataKesehatanBulanan $model): void {
            $model->created_by ??= Auth::id();
            $model->updated_by ??= Auth::id();
        });

        static::saving(function (DataKesehatanBulanan $model): void {
            $model->updated_by = Auth::id();
        });
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        // SPEC-004: Single source of truth — UserScope::visibleTo().
        return UserScope::visibleTo($query);
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function periodePelaporan(): BelongsTo
    {
        return $this->belongsTo(PeriodePelaporan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    /**
     * SPEC-003: Link to the submission batch that this record belongs to.
     * Every OPD record is tied to an approved batch for read visibility.
     */
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function kasusPenyakit(): HasMany
    {
        return $this->hasMany(KasusPenyakit::class, 'data_kesehatan_bulanan_id');
    }

    // Relasi ke model-model baru
    public function ibuHamils(): HasMany
    {
        return $this->hasMany(IbuHamil::class, 'data_kesehatan_bulanan_id');
    }

    public function bayis(): HasMany
    {
        return $this->hasMany(Bayi::class, 'data_kesehatan_bulanan_id');
    }

    public function tenagaKesehatans(): HasMany
    {
        return $this->hasMany(TenagaKesehatan::class, 'data_kesehatan_bulanan_id');
    }

    public function pelayananKesehatans(): HasMany
    {
        return $this->hasMany(PelayananKesehatan::class, 'data_kesehatan_bulanan_id');
    }

    // Accessor untuk menghitung statistik dari relasi
    public function getTotalIbuHamilAttribute(): int
    {
        return $this->ibuHamils()->count();
    }

    public function getTotalBayiAttribute(): int
    {
        return $this->bayis()->count();
    }

    public function getTotalTenagaKesehatanAttribute(): int
    {
        return $this->tenagaKesehatans()->count();
    }

    public function getTotalPelayananAttribute(): int
    {
        return $this->pelayananKesehatans()->count();
    }

    // Accessor untuk menghitung berdasarkan profesi
    public function getCountDokterAttribute(): int
    {
        return $this->tenagaKesehatans()->where('profesi', 'like', 'Dokter%')->count();
    }

    public function getCountPerawatAttribute(): int
    {
        return $this->tenagaKesehatans()->where('profesi', 'Perawat')->count();
    }

    public function getCountBidanAttribute(): int
    {
        return $this->tenagaKesehatans()->where('profesi', 'Bidan')->count();
    }

    public function getCountApotekerAttribute(): int
    {
        return $this->tenagaKesehatans()->where('profesi', 'Apoteker')->count();
    }

    public function getCountAhliGiziAttribute(): int
    {
        return $this->tenagaKesehatans()->where('profesi', 'Ahli Gizi')->count();
    }

    // Accessor untuk menghitung ibu hamil berdasarkan status
    public function getCountIbuHamilAktifAttribute(): int
    {
        return $this->ibuHamils()->where('status_kehamilan', 'Hamil')->count();
    }

    public function getCountIbuMelahirkanAttribute(): int
    {
        return $this->ibuHamils()->where('status_kehamilan', 'Melahirkan')->count();
    }

    public function getCountIbuResikoTinggiAttribute(): int
    {
        return $this->ibuHamils()->where('kategori_resiko', 'Resiko Tinggi')->count();
    }

    // Accessor untuk bayi berdasarkan status
    public function getCountBayiLakiLakiAttribute(): int
    {
        return $this->bayis()->where('jenis_kelamin', 'Laki-laki')->count();
    }

    public function getCountBayiPerempuanAttribute(): int
    {
        return $this->bayis()->where('jenis_kelamin', 'Perempuan')->count();
    }

    public function getCountBayiStuntingAttribute(): int
    {
        return $this->bayis()->where('kategori_balita', 'Stunting')->count();
    }

    public function getCountBayiGiziBurukAttribute(): int
    {
        return $this->bayis()->where('status_gizi', 'Buruk')->count();
    }

}
