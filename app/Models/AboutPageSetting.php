<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPageSetting extends Model
{
    protected $table = 'about_page_settings';

    protected $fillable = [
        'judul_halaman',
        'tagline',
        'banner_image',
        'meta_description',
        'meta_keywords',
        'og_image',
        'updated_by',
    ];

    protected $casts = [
        'updated_by' => 'integer',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
