<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHeroSetting extends Model
{
    protected $fillable = ['judul','deskripsi','gambar','aktif','updated_by'];
    protected $casts = ['aktif' => 'boolean'];
}
