<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class StokObat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stok_obats';

    protected $fillable = [
        'fasilitas_kesehatan_id',
        'submission_batch_id',
        'kode_obat',
        'nama_obat',
        'satuan',
        'kategori',
        'stok_awal',
        'terima_dari_gudang',
        'jumlah_dipakai',
        'stok_akhir',
        'jumlah_rusak',
        'jumlah_kadaluarsa',
        'stok_minimum',
        'tanggal_kadaluarsa',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'stok_awal' => 'integer',
        'terima_dari_gudang' => 'integer',
        'jumlah_dipakai' => 'integer',
        'stok_akhir' => 'integer',
        'jumlah_rusak' => 'integer',
        'jumlah_kadaluarsa' => 'integer',
        'stok_minimum' => 'integer',
        'tanggal_kadaluarsa' => 'date',
    ];

    public function fasilitasKesehatan(): BelongsTo
    {
        return $this->belongsTo(FasilitasKesehatan::class, 'fasilitas_kesehatan_id');
    }

    public function submissionBatch(): BelongsTo
    {
        return $this->belongsTo(SubmissionBatch::class, 'submission_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeMenipis($query)
    {
        return $query->whereColumn('stok_akhir', '<=', 'stok_minimum');
    }

    public function scopeAkanKadaluarsa($query, int $days = 30)
    {
        return $query->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays($days));
    }

    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    public function scopeBySatuan($query, $satuan)
    {
        return $query->where('satuan', $satuan);
    }

    public function isMenipis(): bool
    {
        return $this->stok_akhir <= $this->stok_minimum;
    }

    public function isKadaluarsa(int $days = 30): bool
    {
        return $this->tanggal_kadaluarsa !== null
            && $this->tanggal_kadaluarsa->lte(now()->addDays($days));
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function calculateStokAkhir(): void
    {
        $this->stok_akhir = $this->stok_awal + $this->terima_dari_gudang - $this->jumlah_dipakai;
    }
}
