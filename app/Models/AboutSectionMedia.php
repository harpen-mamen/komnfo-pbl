<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSectionMedia extends Model
{
    protected $table = 'about_section_media';

    protected $fillable = [
        'section_content_id',
        'judul_media',
        'file_path',
        'tipe',
        'caption',
        'urutan',
    ];

    protected $casts = [
        'section_content_id' => 'integer',
        'urutan' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(AboutSectionContent::class, 'section_content_id');
    }
}
