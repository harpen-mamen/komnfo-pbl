<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TentangOpdGallery extends Model
{
    protected $table = 'tentang_opd_galleries';

    protected $fillable = [
        'tentang_opd_id',
        'file_path',
        'caption',
        'urutan',
    ];

    protected $casts = [
        'tentang_opd_id' => 'integer',
        'urutan' => 'integer',
    ];

    public function opd()
    {
        return $this->belongsTo(TentangOpd::class, 'tentang_opd_id');
    }
}
