<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportPendudukLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'import_penduduk_logs';

    protected $fillable = [
        'user_id',
        'kecamatan_id',
        'file_name',
        'total_rows',
        'success_count',
        'fail_count',
        'errors',
        'status',
    ];

    protected $casts = [
        'errors' => 'array',
        'success_count' => 'integer',
        'fail_count' => 'integer',
        'total_rows' => 'integer',
    ];

    /**
     * Get the kecamatan (wilayah) that owns the log.
     */
    public function kecamatan()
    {
        return $this->belongsTo(Wilayah::class, 'kecamatan_id');
    }

    /**
     * Get the user that performed the import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
