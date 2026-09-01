<?php

namespace App\Services\AI;

use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use Illuminate\Support\Facades\Cache;

class SipanduAITools
{
    public function getTotalPenduduk(): int
    {
        return Penduduk::query()->visibleToCurrentUser()->count();
    }

    public function getJumlahKeluarga(): int
    {
        return Keluarga::query()->visibleToCurrentUser()->count();
    }

    public function getTotalRumah(): int
    {
        return Rumah::query()->visibleToCurrentUser()->count();
    }

    public function getStatistikGender(): array
    {
        return Penduduk::query()->visibleToCurrentUser()->selectRaw('jenis_kelamin, COUNT(*) as total')->groupBy('jenis_kelamin')->pluck('total', 'jenis_kelamin')->all();
    }

    public function getStatistikAgama(): array
    {
        return Penduduk::query()->visibleToCurrentUser()->selectRaw('agama, COUNT(*) as total')->groupBy('agama')->pluck('total', 'agama')->all();
    }

    public function getStatistikPendidikan(): array
    {
        return Penduduk::query()->visibleToCurrentUser()->selectRaw('pendidikan, COUNT(*) as total')->groupBy('pendidikan')->pluck('total', 'pendidikan')->all();
    }

    public function getSnapshot(string $question): array
    {
        return Cache::remember('ai:tools:snapshot:'.sha1($question), now()->addMinutes(5), function (): array {
            return [
                'total_penduduk' => $this->getTotalPenduduk(),
                'jumlah_keluarga' => $this->getJumlahKeluarga(),
                'total_rumah' => $this->getTotalRumah(),
            ];
        });
    }
}
