<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumSatuData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'forum_satu_data';

    protected $fillable = [
        'no_sk', 'tanggal_sk', 'berlaku_mulai', 'berlaku_sampai',
        'tentang', 'keterangan', 'is_aktif', 'file_sk_path', 'created_by',
    ];

    protected $casts = [
        'tanggal_sk' => 'date',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function strukturs(): HasMany
    {
        return $this->hasMany(ForumStruktur::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
