<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KecamatanSubmission extends Model
{
    use HasFactory;

    protected $table = 'kecamatan_submissions';

    protected $fillable = [
        'kecamatan_id', 'submitted_by', 'verified_by', 'rumah_count', 'keluarga_count', 'penduduk_count', 'status', 'notes', 'kominfo_notes', 'submitted_at', 'verified_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
