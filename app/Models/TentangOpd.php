<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentangOpd extends Model
{
    protected $table = 'tentang_opd';

    protected $fillable = [
        'opd_id',
        'nama_instansi',
        'singkatan',
        'slug',
        'kategori',
        'logo',
        'alamat',
        'telepon',
        'email',
        'website',
        'kepala_instansi_id',
        'profil_singkat',
        'tupoksi',
        'urutan',
        'tampilkan',
    ];

    protected $casts = [
        'opd_id' => 'integer',
        'kepala_instansi_id' => 'integer',
        'urutan' => 'integer',
        'tampilkan' => 'boolean',
    ];

    public function galleries()
    {
        return $this->hasMany(TentangOpdGallery::class, 'tentang_opd_id')->orderBy('urutan');
    }

    public function opd()
    {
        return $this->belongsTo(Opd::class, 'opd_id');
    }

    public function kepala()
    {
        return $this->belongsTo(PejabatProfile::class, 'kepala_instansi_id');
    }
}
