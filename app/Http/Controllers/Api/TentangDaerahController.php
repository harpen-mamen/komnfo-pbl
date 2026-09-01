<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutPageSetting;
use App\Models\AboutSectionContent;
use App\Models\Kabupaten;
use App\Models\PejabatProfile;
use App\Models\TentangOpd;
use App\Models\TentangOpdGallery;
use App\Models\TentangSejarah;
use App\Models\TentangVisiMisi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TentangDaerahController extends Controller
{
    /**
     * GET /api/v1/public/tentang-daerah
     * Returns aggregate page-level payload: hero/settings + section visibility map.
     */
    public function index(Request $request): JsonResponse
    {
        $payload = Cache::remember('tentang.daerah.index', now()->addMinutes(10), function (): array {
            $setting = AboutPageSetting::query()->first();
            $sections = AboutSectionContent::query()
                ->where('tampil_di_menu', true)
                ->where('status_publikasi', 'published')
                ->orderBy('urutan')
                ->get(['id', 'section_key', 'judul', 'subjudul', 'icon', 'warna_aksen', 'urutan']);

            return [
                'page' => $setting,
                'sections' => $sections,
                'kabupaten' => Kabupaten::query()->first(),
            ];
        });

        return response()->json($payload);
    }

    /**
     * GET /api/v1/public/tentang-daerah/sambutan-bupati
     */
    public function sambutan(Request $request): JsonResponse
    {
        $sambutan = Cache::remember('tentang.daerah.sambutan', now()->addMinutes(10), function () {
            return AboutSectionContent::query()
                ->where('section_key', 'sambutan_bupati')
                ->where('status_publikasi', 'published')
                ->first();
        });

        $bupati = PejabatProfile::query()
            ->whereIn('tipe_pejabat', ['bupati', 'wakil_bupati'])
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(tipe_pejabat, 'bupati','wakil_bupati')")
            ->get();

        return response()->json([
            'section' => $sambutan,
            'pejabat' => $bupati,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/pejabat
     */
    public function pejabat(Request $request): JsonResponse
    {
        $tipe = $request->query('tipe');

        $query = PejabatProfile::query()
            ->where('status', 'aktif')
            ->orderBy('urutan');

        if ($tipe) {
            $query->where('tipe_pejabat', $tipe);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/pejabat/{id}
     */
    public function pejabatDetail(int $id): JsonResponse
    {
        $pejabat = PejabatProfile::query()->findOrFail($id);
        return response()->json(['data' => $pejabat]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/opd
     */
    public function opd(Request $request): JsonResponse
    {
        $query = TentangOpd::query()
            ->where('tampilkan', true)
            ->orderBy('urutan');

        if ($kategori = $request->query('kategori')) {
            $query->where('kategori', $kategori);
        }

        $opds = $query->get(['id', 'nama_instansi', 'singkatan', 'logo', 'slug', 'kategori', 'alamat', 'telepon', 'email', 'website']);

        return response()->json([
            'data' => $opds,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/opd/{slug}
     */
    public function opdDetail(string $slug): JsonResponse
    {
        $opd = TentangOpd::query()->where('slug', $slug)->firstOrFail();
        $galleries = TentangOpdGallery::query()->where('tentang_opd_id', $opd->id)->orderBy('urutan')->get();

        $kepala = $opd->kepala_instansi_id
            ? PejabatProfile::query()->find($opd->kepala_instansi_id)
            : null;

        return response()->json([
            'data' => $opd,
            'galleries' => $galleries,
            'kepala' => $kepala,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/sejarah
     */
    public function sejarah(Request $request): JsonResponse
    {
        $events = Cache::remember('tentang.daerah.sejarah', now()->addMinutes(30), function () {
            return TentangSejarah::query()
                ->where('tampilkan', true)
                ->orderBy('urutan')
                ->orderBy('tahun')
                ->get();
        });

        return response()->json(['data' => $events]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/visi-misi
     */
    public function visiMisi(Request $request): JsonResponse
    {
        $items = Cache::remember('tentang.daerah.visimisi', now()->addMinutes(30), function () {
            return TentangVisiMisi::query()->orderBy('tipe')->orderBy('urutan')->get();
        });

        $grouped = $items->groupBy('tipe')->map(fn ($rows) => $rows->pluck('konten'));

        return response()->json([
            'visi' => $grouped->get('visi', collect())->first(),
            'misi' => $grouped->get('misi', collect())->values(),
            'tujuan' => $grouped->get('tujuan', collect())->values(),
            'sasaran' => $grouped->get('sasaran', collect())->values(),
            'periode' => $items->first()?->periode,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/geografi
     */
    public function geografi(Request $request): JsonResponse
    {
        $section = AboutSectionContent::query()
            ->where('section_key', 'geografi')
            ->where('status_publikasi', 'published')
            ->first();

        $kabupaten = Kabupaten::query()->first();

        return response()->json([
            'section' => $section,
            'kabupaten' => $kabupaten,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/lambang
     */
    public function lambang(Request $request): JsonResponse
    {
        $section = AboutSectionContent::query()
            ->where('section_key', 'lambang_daerah')
            ->where('status_publikasi', 'published')
            ->first();

        return response()->json(['data' => $section]);
    }
}
