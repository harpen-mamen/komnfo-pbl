<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutSectionContent extends Model
{
    protected $table = 'about_section_contents';

    protected $fillable = [
        'section_key',
        'judul',
        'subjudul',
        'ringkasan',
        'konten',
        'icon',
        'warna_aksen',
        'urutan',
        'status_publikasi',
        'tampil_di_menu',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'urutan' => 'integer',
        'tampil_di_menu' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function media()
    {
        return $this->hasMany(AboutSectionMedia::class, 'section_content_id')->orderBy('urutan');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
