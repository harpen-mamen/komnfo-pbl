<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'audit_trails';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'field',
        'nilai_lama',
        'nilai_baru',
        'aksi',
        'pengguna_id',
        'pengguna',
        'role',
        'ip',
        'browser',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function penggunaModel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengguna_id');
    }
}
