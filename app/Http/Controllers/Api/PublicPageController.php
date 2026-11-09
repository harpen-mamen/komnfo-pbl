<?php

namespace App\Http\Controllers\Api;

use App\Models\Wilayah;
use App\Models\Dataset;
use App\Models\DatasetValue;
use App\Models\PortalContent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class PublicPageController extends Controller
{
    public function hero(): JsonResponse
    {
        $hero = Cache::remember('public.hero.v4', now()->addMinute(), function () {
            if (! Schema::hasTable('portal_contents')) return null;
            $content = PortalContent::query()->where('type', 'hero')->where('is_published', true)
                ->whereNotNull('published_at')->where('published_at', '<=', now())->latest('published_at')->first();
            return $content ? [
                'judul' => $content->title,
                'deskripsi' => $content->summary,
                'konten' => $content->body,
                'gambar' => $this->mediaUrl($content->image_path),
                'video' => $this->mediaUrl($content->video_path),
                'published_at' => $content->published_at?->toIso8601String(),
            ] : null;
        });

        return response()->json(['data' => $hero]);
    }

    public function home(): JsonResponse
    {
        $stats = Cache::remember('public.home.stats', now()->addMinutes(2), function (): array {
            return [
                ['label' => 'Penduduk', 'value' => $this->publishedAggregate('3.13', ['penduduk_laki', 'penduduk_perempuan', 'total'])],
                ['label' => 'Keluarga', 'value' => $this->publishedAggregate('3.12', ['kk_laki', 'kk_perempuan', 'total'])],
                ['label' => 'Desa', 'value' => Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->count()],
                ['label' => 'Kecamatan', 'value' => Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->count()],
            ];
        });

        $hero = $this->hero()->getData(true)['data'] ?? null;
        $berita = Schema::hasTable('portal_contents') ? PortalContent::query()
            ->where('type', 'news')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())
            ->latest('published_at')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'summary', 'image_path', 'category', 'published_at'])
            ->map(fn (PortalContent $item): array => [
                'id' => $item->id, 'judul' => $item->title, 'slug' => $item->slug,
                'ringkasan' => $item->summary, 'gambar' => $this->mediaUrl($item->image_path),
                'kategori' => $item->category, 'terbit_at' => $item->published_at?->toIso8601String(),
            ]) : collect();

        return response()->json([
            'stats' => $stats,
            'hero' => $hero,
            'berita' => $berita,
        ]);
    }

    private function publishedAggregate(string $code, array $fields): int|float
    {
        $owner = 'DUKCAPIL';
        $values = DatasetValue::query()->where('status', DatasetValue::STATUS_PUBLISHED)->whereNotNull('published_at')
            ->where(fn ($notes) => $notes->whereNull('notes')->orWhere('notes', 'not like', '[DEMO-%'))
            ->whereHas('dataset', fn ($query) => $query->where('is_active', true)->whereIn('lisensi', [Dataset::LISENSI_CCBY, Dataset::LISENSI_OPEN])
                ->where(fn ($codes) => $codes->where('kode', $code)->orWhere('kode_sdi', $code))->whereHas('opd', fn ($opd) => $opd->where('kode', $owner)))
            ->with('dataset:id,kode,kode_sdi')->get();
        $year = $values->filter(fn (DatasetValue $value) => (string) ($value->dataset?->kode_sdi ?: $value->dataset?->kode) === $code)->max('tahun');
        return $values->filter(fn (DatasetValue $value) => $value->tahun === $year && (string) ($value->dataset?->kode_sdi ?: $value->dataset?->kode) === $code && $value->wilayah_type === Wilayah::TIPE_KECAMATAN)
            ->sum(function (DatasetValue $value) use ($fields) {
                foreach ($fields as $field) {
                    $number = data_get($value->values, $field);
                    if (is_numeric($number)) return (float) $number;
                }
                return 0;
            });
    }

    public function layanan(): JsonResponse
    {
        $services = Schema::hasTable('portal_contents') ? PortalContent::query()
            ->where('type', 'service')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())
            ->orderBy('sort_order')->orderBy('title')->get(['id', 'title', 'summary', 'category', 'slug'])
            ->map(fn (PortalContent $item): array => [
                'id' => $item->id, 'nama' => $item->title, 'deskripsi' => $item->summary,
                'icon' => $item->category ?: 'document', 'slug' => $item->slug,
            ])->values() : collect();

        return response()->json([
            'data' => [
                'judul' => 'Layanan Publik',
                'deskripsi' => 'Daftar layanan publik yang tersedia di lingkungan Pemerintah Kabupaten Kepulauan Sangihe.',
                'layanan' => $services,
            ],
        ]);
    }

    private function mediaUrl(?string $path): ?string
    {
        if (! $path) return null;
        return '/media/' . ltrim($path, '/');
    }
}
