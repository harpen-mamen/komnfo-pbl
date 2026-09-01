<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PejabatProfile extends Model
{
    protected $table = 'pejabat_profiles';

    protected $fillable = [
        'tipe_pejabat',
        'nama_lengkap',
        'gelar_depan',
        'gelar_belakang',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'jabatan',
        'foto',
        'biodata_singkat',
        'riwayat_pendidikan',
        'riwayat_jabatan',
        'penghargaan',
        'tanda_tangan',
        'tanggal_mulai',
        'tanggal_selesai',
        'urutan',
        'status',
        'published_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'published_at' => 'datetime',
        'urutan' => 'integer',
        'riwayat_pendidikan' => 'array',
        'riwayat_jabatan' => 'array',
        'penghargaan' => 'array',
    ];

    public function scopeActive($q)
    {
        return $q->where('status', 'aktif');
    }

    public function scopePublished($q)
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getNamaLengkapGelarAttribute(): string
    {
        return trim(($this->gelar_depan ? $this->gelar_depan . ' ' : '') . $this->nama_lengkap . ($this->gelar_belakang ? ', ' . $this->gelar_belakang : ''));
    }
}
