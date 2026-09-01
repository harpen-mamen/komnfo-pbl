<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentangSejarah extends Model
{
    protected $table = 'tentang_sejarah';

    protected $fillable = [
        'tahun',
        'judul_peristiwa',
        'deskripsi',
        'gambar',
        'icon',
        'urutan',
        'tampilkan',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'tampilkan' => 'boolean',
    ];
}
