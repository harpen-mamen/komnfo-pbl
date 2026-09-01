<?php

namespace App\Models;

use App\Support\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataPrasarana extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_prasaranas';

    protected $fillable = [
        'sekolah_id',
        'submission_batch_id',
        'tahun_ajaran',
        'semester',
        'jumlah_ruang_kelas',
        'kondisi_ruang_baik',
        'rusak_ringan',
        'rusak_berat',
        'laboratorium_ipa',
        'laboratorium_komputer',
        'laboratorium_bahasa',
        'perpustakaan',
        'uks',
        'musholla',
        'sarana_olahraga',
        'sumber_listrik',
        'sumber_internet',
        'kondisi_gedung',
        'catatan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'jumlah_ruang_kelas' => 'integer',
        'kondisi_ruang_baik' => 'integer',
        'rusak_ringan' => 'integer',
        'rusak_berat' => 'integer',
        'laboratorium_ipa' => 'boolean',
        'laboratorium_komputer' => 'boolean',
        'laboratorium_bahasa' => 'boolean',
        'perpustakaan' => 'boolean',
        'uks' => 'boolean',
        'musholla' => 'boolean',
        'sarana_olahraga' => 'boolean',
        'sumber_listrik' => 'boolean',
        'sumber_internet' => 'boolean',
    ];

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
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

    public function scopeByTahunAjaran($query, $tahun)
    {
        return $query->where('tahun_ajaran', $tahun);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeGanjil($query)
    {
        return $query->where('semester', 'ganjil');
    }

    public function scopeGenap($query)
    {
        return $query->where('semester', 'genap');
    }

    public function scopeVisibleToCurrentUser(Builder $query): Builder
    {
        return UserScope::visibleTo($query);
    }

    public function getTotalRuangKelas(): int
    {
        return $this->kondisi_ruang_baik + $this->rusak_ringan + $this->rusak_berat;
    }

    public function getPersentaseKondisiBaik(): float
    {
        $total = $this->getTotalRuangKelas();
        if ($total == 0) {
            return 0;
        }
        return round(($this->kondisi_ruang_baik / $total) * 100, 1);
    }

    public function getPersentaseRusak(): float
    {
        $total = $this->getTotalRuangKelas();
        if ($total == 0) {
            return 0;
        }
        $rusak = $this->rusak_ringan + $this->rusak_berat;
        return round(($rusak / $total) * 100, 1);
    }

    public function getSaranaCount(): int
    {
        $count = 0;
        $sarana = [
            $this->laboratorium_ipa,
            $this->laboratorium_komputer,
            $this->laboratorium_bahasa,
            $this->perpustakaan,
            $this->uks,
            $this->musholla,
            $this->sarana_olahraga,
            $this->sumber_listrik,
            $this->sumber_internet,
        ];
        foreach ($sarana as $s) {
            if ($s) {
                $count++;
            }
        }
        return $count;
    }
}
