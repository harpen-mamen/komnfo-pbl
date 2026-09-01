<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class FasilitasKesehatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fasilitas_kesehatans';

    protected $fillable = [
        'kecamatan_id',
        'desa_id',
        'nama',
        'kode_faskes',
        'jenis',
        'alamat',
        'telepon',
        'email',
        'kepala_puskes',
        'nip_kepala',
        'tahun_berdiri',
        'status_operasional',
        'koordinat_lat',
        'koordinat_lng',
        'jumlah_posyandu',
        'jumlah_pustu',
        'jumlah_kader',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status_operasional' => 'boolean',
        'koordinat_lat' => 'decimal:7',
        'koordinat_lng' => 'decimal:7',
        'tahun_berdiri' => 'integer',
        'jumlah_posyandu' => 'integer',
        'jumlah_pustu' => 'integer',
        'jumlah_kader' => 'integer',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    /**
     * Get the latest submission batch that includes this faskes data.
     * Joins through child tables that carry the submission_batch_id FK.
     */
    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class);
    }

    /**
     * Get the most recent batch that includes this faskes.
     * Query-based alternative since master entities don't have FK directly.
     */
    public function getLatestBatchAttribute(): ?SubmissionBatch
    {
        $faskesId = $this->id;

        return SubmissionBatch::query()
            ->where('module', SubmissionBatch::MODULE_PUSKES)
            ->whereNotNull('submitted_at')
            ->where(function ($q) use ($faskesId) {
                $q->whereIn('id', StokObat::where('fasilitas_kesehatan_id', $faskesId)->whereNotNull('submission_batch_id')->select('submission_batch_id'))
                    ->orWhereIn('id', RekamMedis::where('fasilitas_kesehatan_id', $faskesId)->whereNotNull('submission_batch_id')->select('submission_batch_id'))
                    ->orWhereIn('id', TenagaKesehatan::where('fasilitas_kesehatan_id', $faskesId)->whereNotNull('submission_batch_id')->select('submission_batch_id'));
            })
            ->orderByDesc('submitted_at')
            ->first();
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

    public function stokObats(): HasMany
    {
        return $this->hasMany(StokObat::class, 'fasilitas_kesehatan_id');
    }

    public function rekamMedis(): HasMany
    {
        return $this->hasMany(RekamMedis::class, 'fasilitas_kesehatan_id');
    }

    public function tenagaKesehatans(): HasMany
    {
        return $this->hasMany(TenagaKesehatan::class, 'fasilitas_kesehatan_id');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_operasional', true);
    }

    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopePuskesmas($query)
    {
        return $query->where('jenis', 'Puskesmas');
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public static function generateKodeFaskes(Kecamatan $kecamatan, string $jenis): string
    {
        $prefix = match ($jenis) {
            'Puskesmas' => 'PKM',
            'Pusat Pembantu' => 'PST',
            'Posyandu' => 'PSY',
            'Klinik' => 'KLK',
            default => 'FKS',
        };

        $kecCode = strtoupper(substr($kecamatan->nama ?? 'UNK', 0, 3));
        $count = static::withTrashed()
            ->where('kecamatan_id', $kecamatan->id)
            ->where('jenis', $jenis)
            ->count();
        $seq = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$kecCode}-{$seq}";
    }

    public function getSummaryStats(): array
    {
        return [
            'total_stok_obat' => $this->stokObats()->count(),
            'total_rekam_medis' => $this->rekamMedis()->count(),
            'total_tenaga_kesehatan' => $this->tenagaKesehatans()->count(),
            'stok_menipis' => $this->stokObats()->whereColumn('stok_akhir', '<=', 'stok_minimum')->count(),
        ];
    }
}
