<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicHeroSetting;
use App\Models\PublicNews;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PublicPageController extends Controller
{
    public function hero(): JsonResponse
    {
        $hero = Cache::remember('public.hero', now()->addMinutes(10), function () {
            return PublicHeroSetting::query()->where('aktif', true)->latest()->first();
        });

        return response()->json(['data' => $hero]);
    }

    public function home(): JsonResponse
    {
        $stats = Cache::remember('public.home.stats', now()->addMinutes(2), function (): array {
            return [
                ['label' => 'Penduduk terdata', 'value' => \App\Models\Penduduk::query()->inApprovedBatch()->count()],
                ['label' => 'Keluarga',         'value' => \App\Models\Keluarga::query()->inApprovedBatch()->count()],
                ['label' => 'Desa terpetakan',  'value' => \App\Models\Desa::query()->count()],
                ['label' => 'Kecamatan',        'value' => \App\Models\Kecamatan::query()->count()],
            ];
        });

        $hero = $this->hero()->getData(true)['data'] ?? null;
        $berita = PublicNews::query()
            ->where('terbit', true)
            ->latest('terbit_at')
            ->limit(6)
            ->get(['id', 'judul', 'slug', 'ringkasan', 'gambar', 'kategori', 'terbit_at']);

        return response()->json([
            'stats' => $stats,
            'hero' => $hero,
            'berita' => $berita,
        ]);
    }

    public function layanan(): JsonResponse
    {
        return response()->json([
            'data' => [
                'judul' => 'Layanan Publik',
                'deskripsi' => 'Daftar layanan publik yang tersedia di lingkungan Pemerintah Kabupaten Kepulauan Sangihe.',
                'layanan' => [
                    ['nama' => 'Penerbitan Surat Keterangan', 'icon' => 'document'],
                    ['nama' => 'Pengaduan Masyarakat', 'icon' => 'megaphone'],
                    ['nama' => 'Permohonan Data', 'icon' => 'database'],
                    ['nama' => 'Konsultasi Publik', 'icon' => 'chat'],
                ],
            ],
        ]);
    }
}
