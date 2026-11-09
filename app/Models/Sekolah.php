<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\UserScope;

class Sekolah extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'sekolahs';

    protected $fillable = [
        'kecamatan_id',
        'desa_id',
        'nama_sekolah',
        'npsn',
        'nss',
        'jenjang',
        'status',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_sekolah',
        'nip_kepala',
        'tahun_berdiri',
        'akreditasi',
        'kurikulum',
        'koordinat_lat',
        'koordinat_lng',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
        'tahun_berdiri' => 'integer',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the latest submission batch that includes this school.
     */
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    /**
     * Get the most recent batch that includes this school.
     */
    public function getLatestBatchAttribute(): ?SubmissionBatch
    {
        $sekolahId = $this->id;

        return SubmissionBatch::query()
            ->where('module', SubmissionBatch::MODULE_SEKOLAH)
            ->whereNotNull('submitted_at')
            ->where(function ($q) use ($sekolahId) {
                $q->whereIn('id', DataGuru::where('sekolah_id', $sekolahId)->whereNotNull('submission_batch_id')->select('submission_batch_id'))
                    ->orWhereIn('id', DataSiswa::where('sekolah_id', $sekolahId)->whereNotNull('submission_batch_id')->select('submission_batch_id'))
                    ->orWhereIn('id', DataPrasarana::where('sekolah_id', $sekolahId)->whereNotNull('submission_batch_id')->select('submission_batch_id'));
            })
            ->orderByDesc('submitted_at')
            ->first();
    }

    public function dataGurus(): HasMany
    {
        return $this->hasMany(DataGuru::class, 'sekolah_id');
    }

    public function dataSiswas(): HasMany
    {
        return $this->hasMany(DataSiswa::class, 'sekolah_id');
    }

    public function dataPrasaranas(): HasMany
    {
        return $this->hasMany(DataPrasarana::class, 'sekolah_id');
    }

    public function scopeByJenjang($query, $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNegeri($query)
    {
        return $query->where('status', 'Negeri');
    }

    public function scopeSwasta($query)
    {
        return $query->where('status', 'Swasta');
    }

    public function scopeSd($query)
    {
        return $query->where('jenjang', 'SD');
    }

    public function scopeSmp($query)
    {
        return $query->where('jenjang', 'SMP');
    }

    public function scopeSma($query)
    {
        return $query->where('jenjang', 'SMA');
    }

    public function scopeSmk($query)
    {
        return $query->where('jenjang', 'SMK');
    }

    public static function jenjangOptions(): array
    {
        return ['SD', 'SMP', 'SMA', 'SMK', 'SLB'];
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function getLatestData(string $tahunAjaran, string $semester): ?array
    {
        $guru = $this->dataGurus()->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)->first();
        $siswa = $this->dataSiswas()->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)->first();
        $prasarana = $this->dataPrasaranas()->where('tahun_ajaran', $tahunAjaran)->where('semester', $semester)->first();

        return [
            'guru' => $guru,
            'siswa' => $siswa,
            'prasarana' => $prasarana,
        ];
    }
}
