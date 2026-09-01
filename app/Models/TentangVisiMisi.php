<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentangVisiMisi extends Model
{
    protected $table = 'tentang_visi_misi';

    protected $fillable = [
        'tipe',
        'konten',
        'urutan',
        'periode',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];
}
