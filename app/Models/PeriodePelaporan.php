<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PeriodePelaporan extends Model
{
    use HasFactory;

    public const STATUS = [
        'Draft' => 'Draft',
        'Dibuka' => 'Dibuka',
        'Ditutup' => 'Ditutup',
        'Diverifikasi' => 'Diverifikasi',
        'Selesai' => 'Selesai',
    ];

    protected $table = 'periode_pelaporans';

    protected $fillable = [
        'nama_periode',
        'bulan',
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function scopeDibuka(Builder $query): Builder
    {
        return $query->where('status', 'Dibuka');
    }

    public function scopeAktif(Builder $query): Builder
    {
        $today = Carbon::today();

        return $query
            ->dibuka()
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today);
    }

    public function isOpenForInput(): bool
    {
        $today = Carbon::today();

        return $this->status === 'Dibuka'
            && $this->tanggal_mulai?->lte($today)
            && $this->tanggal_selesai?->gte($today);
    }

    public function getRemainingDaysAttribute(): int
    {
        if (! $this->tanggal_selesai) {
            return 0;
        }

        return max(0, Carbon::today()->diffInDays($this->tanggal_selesai, false));
    }

    // Note: DataPendidikanBulanan and DataKesehatanBulanan tables removed
    // Relationships to these tables have been removed
}
