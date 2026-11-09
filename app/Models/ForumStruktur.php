<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumStruktur extends Model
{
    use HasFactory;

    protected $table = 'forum_strukturs';

    protected $fillable = [
        'forum_satu_data_id', 'peran', 'nama_pejabat', 'nip', 'jabatan_asal',
        'opd_id', 'user_id', 'kontak', 'is_aktif',
        'tanggal_mulai', 'tanggal_selesai',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(ForumSatuData::class, 'forum_satu_data_id');
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
