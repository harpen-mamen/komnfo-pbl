<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutPageSetting;
use App\Models\AboutSectionContent;
use App\Models\Wilayah;
use App\Models\PejabatProfile;
use App\Models\TentangOpd;
use App\Models\TentangOpdGallery;
use App\Models\TentangSejarah;
use App\Models\TentangVisiMisi;
use App\Models\PortalContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class TentangDaerahController extends Controller
{
    /**
     * GET /api/v1/public/tentang-daerah
     * Returns aggregate page-level payload: hero/settings + section visibility map.
     */
    public function index(Request $request): JsonResponse
    {
        $payload = Cache::remember('tentang.daerah.index.v2', now()->addMinute(), function (): array {
            $setting = Schema::hasTable('about_page_settings') ? AboutPageSetting::query()->first(['judul_halaman', 'tagline', 'banner_image', 'meta_description', 'meta_keywords', 'og_image']) : null;
            if (! $setting && Schema::hasTable('portal_contents')) {
                $item = PortalContent::query()->where('type', 'page')->where('slug', 'tentang-daerah')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->first(['title', 'summary', 'image_path']);
                $setting = $item ? ['judul_halaman' => $item->title, 'tagline' => $item->summary, 'banner_image' => $item->image_path] : null;
            }
            $sections = Schema::hasTable('about_section_contents') ? AboutSectionContent::query()
                ->where('tampil_di_menu', true)
                ->where('status_publikasi', 'published')
                ->orderBy('urutan')
                ->get(['id', 'section_key', 'judul', 'subjudul', 'icon', 'warna_aksen', 'urutan']) : collect();

            if ($sections->isEmpty() && Schema::hasTable('portal_contents')) {
                $sections = PortalContent::query()->where('type', 'page')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())
                    ->orderBy('sort_order')->get(['id', 'title', 'slug', 'summary', 'sort_order'])
                    ->map(fn (PortalContent $item): array => ['id' => $item->id, 'section_key' => $item->slug, 'judul' => $item->title, 'subjudul' => $item->summary, 'icon' => null, 'warna_aksen' => null, 'urutan' => $item->sort_order]);
            }
            if ($sections->isEmpty()) {
                $sections = collect([
                    ['id' => 'visi_misi', 'section_key' => 'visi_misi', 'judul' => 'Visi & Misi', 'subjudul' => 'Arah pembangunan daerah', 'icon' => 'eye', 'warna_aksen' => null, 'urutan' => 1],
                    ['id' => 'sejarah', 'section_key' => 'sejarah', 'judul' => 'Sejarah Singkat', 'subjudul' => 'Perjalanan Sangihe', 'icon' => 'clock', 'warna_aksen' => null, 'urutan' => 2],
                    ['id' => 'geografi', 'section_key' => 'geografi', 'judul' => 'Geografi', 'subjudul' => 'Wilayah kepulauan', 'icon' => 'map', 'warna_aksen' => null, 'urutan' => 3],
                    ['id' => 'lambang_daerah', 'section_key' => 'lambang_daerah', 'judul' => 'Lambang Daerah', 'subjudul' => 'Simbol dan makna', 'icon' => 'shield', 'warna_aksen' => null, 'urutan' => 4],
                ]);
            }

            return [
                'page' => $setting ?: [
                    'judul_halaman' => 'Tentang Kabupaten Kepulauan Sangihe',
                    'tagline' => 'Ketahui segala tentang Sangihe mulai dari pemerintahan, program, hingga kondisi terkini.',
                    'banner_image' => null,
                ],
                'sections' => $sections,
                'kabupaten' => Wilayah::query()->where('tipe', Wilayah::TIPE_KABUPATEN)->where('aktif', true)->first(['id', 'kode_bps', 'nama']),
            ];
        });

        return response()->json($payload);
    }

    /**
     * GET /api/v1/public/tentang-daerah/sambutan-bupati
     */
    public function sambutan(Request $request): JsonResponse
    {
        $sambutan = Cache::remember('tentang.daerah.sambutan', now()->addMinute(), function () {
            $legacy = Schema::hasTable('about_section_contents') ? AboutSectionContent::query()
                ->where('section_key', 'sambutan_bupati')
                ->where('status_publikasi', 'published')
                ->first(['judul', 'subjudul', 'konten']) : null;
            if ($legacy) return $legacy;
            $item = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'page')->where('slug', 'sambutan-bupati')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->first(['title', 'summary', 'body']) : null;
            return $item ? ['judul' => $item->title, 'subjudul' => $item->summary, 'konten' => $item->body] : null;
        });

        $bupati = Schema::hasTable('pejabat_profiles') ? PejabatProfile::query()
            ->whereIn('tipe_pejabat', ['bupati', 'wakil_bupati'])
            ->where('status', 'aktif')
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->orderByRaw("FIELD(tipe_pejabat, 'bupati','wakil_bupati')")
            ->get(['tipe_pejabat', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'jabatan', 'foto', 'biodata_singkat', 'urutan']) : collect();
        if ($bupati->isEmpty() && Schema::hasTable('portal_contents')) {
            $bupati = PortalContent::query()->where('type', 'official')->whereIn('category', ['bupati', 'wakil_bupati'])->where('is_published', true)
                ->whereNotNull('published_at')->where('published_at', '<=', now())->orderByRaw("FIELD(category, 'bupati','wakil_bupati')")
                ->get(['id', 'category as tipe_pejabat', 'title as nama_lengkap', 'summary as jabatan', 'image_path as foto', 'body as biodata_singkat'])
                ->map(fn ($item) => array_merge($item->toArray(), ['gelar_depan' => null, 'gelar_belakang' => null]));
        }

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

        if (! Schema::hasTable('pejabat_profiles')) {
            $data = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'official')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('sort_order')
                ->when($tipe, fn ($query) => $query->where('category', $tipe))
                ->get(['id', 'category as tipe_pejabat', 'title as nama_lengkap', 'summary as jabatan', 'image_path as foto', 'body as biodata_singkat', 'sort_order as urutan'])
                ->map(fn ($item) => array_merge($item->toArray(), ['gelar_depan' => null, 'gelar_belakang' => null])) : collect();
            return response()->json(['data' => $data]);
        }
        $query = PejabatProfile::query()
            ->where('status', 'aktif')
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->orderBy('urutan');

        if ($tipe) {
            $query->where('tipe_pejabat', $tipe);
        }

        return response()->json([
            'data' => $query->get(['id', 'tipe_pejabat', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'jabatan', 'foto', 'biodata_singkat', 'riwayat_pendidikan', 'riwayat_jabatan', 'penghargaan', 'tanggal_mulai', 'tanggal_selesai', 'urutan']),
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/pejabat/{id}
     */
    public function pejabatDetail(int $id): JsonResponse
    {
        if (! Schema::hasTable('pejabat_profiles')) {
            $item = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'official')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->find($id) : null;
            abort_unless($item, 404, 'Data pejabat belum tersedia.');
            return response()->json(['data' => ['id' => $item->id, 'tipe_pejabat' => $item->category, 'nama_lengkap' => $item->title, 'jabatan' => $item->summary, 'foto' => $item->image_path, 'biodata_singkat' => $item->body, 'gelar_depan' => null, 'gelar_belakang' => null]]);
        }
        $pejabat = PejabatProfile::query()->where('status', 'aktif')->whereNotNull('published_at')->where('published_at', '<=', now())
            ->findOrFail($id, ['id', 'tipe_pejabat', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'jabatan', 'foto', 'biodata_singkat', 'riwayat_pendidikan', 'riwayat_jabatan', 'penghargaan', 'tanggal_mulai', 'tanggal_selesai', 'urutan']);
        return response()->json(['data' => $pejabat]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/opd
     */
    public function opd(Request $request): JsonResponse
    {
        if (! Schema::hasTable('tentang_opds')) {
            $data = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'opd_profile')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('sort_order')
                ->get(['id', 'title as nama_instansi', 'slug', 'category as kategori', 'image_path as logo', 'summary as profil_singkat', 'abbreviation as singkatan', 'address as alamat', 'phone as telepon', 'contact_email as email', 'website']) : collect();
            return response()->json(['data' => $data]);
        }
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
        if (! Schema::hasTable('tentang_opds')) {
            $item = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'opd_profile')->where('slug', $slug)->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->first() : null;
            abort_unless($item, 404, 'Profil OPD belum tersedia.');
            return response()->json(['data' => [
                'id' => $item->id, 'nama_instansi' => $item->title, 'singkatan' => $item->abbreviation, 'slug' => $item->slug,
                'kategori' => $item->category ?? 'lainnya', 'logo' => $item->image_path, 'profil_singkat' => $item->summary,
                'tupoksi' => $item->body, 'alamat' => $item->address, 'telepon' => $item->phone, 'email' => $item->contact_email, 'website' => $item->website,
            ], 'galleries' => [], 'kepala' => null]);
        }
        $opd = TentangOpd::query()->where('tampilkan', true)->where('slug', $slug)->firstOrFail();
        $publicOpd = $opd->only(['id', 'nama_instansi', 'singkatan', 'slug', 'kategori', 'logo', 'alamat', 'telepon', 'email', 'website', 'profil_singkat', 'tupoksi']);
        $galleries = Schema::hasTable('tentang_opd_galleries') ? TentangOpdGallery::query()->where('tentang_opd_id', $opd->id)->orderBy('urutan')->get(['id', 'file_path', 'caption', 'urutan']) : collect();

        $kepala = $opd->kepala_instansi_id
            ? PejabatProfile::query()->where('status', 'aktif')->whereNotNull('published_at')->where('published_at', '<=', now())->find($opd->kepala_instansi_id, ['id', 'tipe_pejabat', 'nama_lengkap', 'gelar_depan', 'gelar_belakang', 'jabatan', 'foto', 'biodata_singkat'])
            : null;

        return response()->json([
            'data' => $publicOpd,
            'galleries' => $galleries,
            'kepala' => $kepala,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/sejarah
     */
    public function sejarah(Request $request): JsonResponse
    {
        $events = Cache::remember('tentang.daerah.sejarah.v4', now()->addMinute(), function () {
            $legacy = Schema::hasTable('tentang_sejarahs') ? TentangSejarah::query()
                ->where('tampilkan', true)
                ->orderBy('urutan')
                ->orderBy('tahun')
                ->get(['tahun', 'judul_peristiwa', 'deskripsi', 'gambar', 'icon', 'urutan']) : collect();
            if ($legacy->isNotEmpty()) return $legacy;
            $published = Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'history')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('sort_order')->orderBy('year')->get(['year as tahun', 'title as judul_peristiwa', 'body as deskripsi', 'image_path as gambar', 'category as icon', 'sort_order as urutan']) : collect();
            if ($published->isNotEmpty()) return $published;

            return collect([
                ['id' => 1, 'tahun' => 'Abad ke-5', 'judul_peristiwa' => 'Permukiman awal', 'deskripsi' => 'Situs resmi daerah mencatat keberadaan permukiman dan pemerintahan awal di wilayah Sangihe pada periode ini.', 'gambar' => null, 'icon' => 'history', 'urutan' => 1],
                ['id' => 2, 'tahun' => 'Abad ke-8', 'judul_peristiwa' => 'Kerajaan dan kebudayaan', 'deskripsi' => 'Perkembangan kerajaan lokal dan kebudayaan masyarakat kepulauan.', 'gambar' => null, 'icon' => 'history', 'urutan' => 2],
                ['id' => 3, 'tahun' => 'Abad ke-16–17', 'judul_peristiwa' => 'Perdagangan dan masa kolonial', 'deskripsi' => 'Sangihe memasuki jaringan perdagangan regional dan periode kolonial.', 'gambar' => null, 'icon' => 'history', 'urutan' => 3],
                ['id' => 4, 'tahun' => '1925', 'judul_peristiwa' => 'Pembentukan wilayah administratif', 'deskripsi' => 'Situs resmi mencatat pembentukan wilayah administratif pada tahun 1925.', 'gambar' => null, 'icon' => 'history', 'urutan' => 4],
                ['id' => 5, 'tahun' => '1945–1950', 'judul_peristiwa' => 'Bergabung dengan Republik Indonesia', 'deskripsi' => 'Setelah kemerdekaan Indonesia, pemerintahan daerah setempat terus diperkuat.', 'gambar' => null, 'icon' => 'history', 'urutan' => 5],
            ]);
        });

        return response()->json(['data' => $events]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/visi-misi
     */
    public function visiMisi(Request $request): JsonResponse
    {
        $items = Cache::remember('tentang.daerah.visimisi.v4', now()->addMinute(), function () {
            $legacy = Schema::hasTable('tentang_visi_misis') ? TentangVisiMisi::query()->orderBy('tipe')->orderBy('urutan')->get(['tipe', 'konten', 'periode']) : collect();
            if ($legacy->isNotEmpty()) return $legacy;
            return Schema::hasTable('portal_contents') ? PortalContent::query()->where('type', 'vision')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('sort_order')->get(['category as tipe', 'body as konten', 'year as periode']) : collect();
        });

        if ($items->isEmpty()) {
            return response()->json([
                'visi' => 'Muda Berkarya, Wujudkan Sangihe Lebih Sejahtera dan Berbudaya',
                'tagline' => 'Maju, Inklusif, Melayani',
                'misi' => ['SAPTA MEMBARA atau PITU MAKAWANTUGE', 'Mewujudkan Kepulauan Sangihe sebagai Beranda Depan NKRI yang Sejahtera'],
                'cita_cita' => [
                    'Pembangunan infrastruktur jalan.',
                    'Pembangunan jaringan irigasi.',
                    'Peningkatan kualitas pendidikan.',
                    'Penguatan layanan kesehatan.',
                    'Pengembangan ekonomi kreatif dan UMKM.',
                    'Reaktivasi konektivitas transportasi.',
                    'Program ketahanan pangan.',
                ],
                'tujuan' => [], 'sasaran' => [], 'periode' => '2025–2029',
                'sumber' => 'https://sangihekab.go.id/tentang-daerah/',
            ]);
        }

        $grouped = $items->groupBy('tipe')->map(fn ($rows) => $rows->pluck('konten'));

        return response()->json([
            'visi' => $grouped->get('visi', collect())->first(),
            'misi' => $grouped->get('misi', collect())->values(),
            'tujuan' => $grouped->get('tujuan', collect())->values(),
            'sasaran' => $grouped->get('sasaran', collect())->values(),
            'periode' => $items->first()?->periode === null ? null : (string) $items->first()->periode,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/geografi
     */
    public function geografi(Request $request): JsonResponse
    {
        $section = Schema::hasTable('about_section_contents') ? AboutSectionContent::query()
            ->where('section_key', 'geografi')
            ->where('status_publikasi', 'published')
            ->first(['judul', 'subjudul', 'konten']) : null;
        if (! $section && Schema::hasTable('portal_contents')) {
            $item = PortalContent::query()->where('type', 'page')->where('slug', 'geografi')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->first(['title', 'summary', 'body']);
            $section = $item ? ['judul' => $item->title, 'subjudul' => $item->summary, 'konten' => $item->body] : null;
        }

        $kabupaten = Wilayah::query()->where('tipe', Wilayah::TIPE_KABUPATEN)->where('aktif', true)->first(['id', 'kode_bps', 'nama']);

        return response()->json([
            'section' => $section ?: [
                'judul' => 'Geografi Kabupaten Kepulauan Sangihe',
                'subjudul' => 'Wilayah kepulauan di Provinsi Sulawesi Utara',
                'konten' => 'Kabupaten Kepulauan Sangihe merupakan wilayah kepulauan di Provinsi Sulawesi Utara. Pusat pemerintahan kabupaten berada di Tahuna. Karakter wilayah kepulauan menjadikan konektivitas antarpulau dan layanan dasar sebagai bagian penting pembangunan daerah.\n\nAlamat Pemerintah Kabupaten: Gedung Pemda, Jl. Malahasa, Kelurahan Soataloara I, Kecamatan Tahuna, Kabupaten Kepulauan Sangihe, Sulawesi Utara.',
            ],
            'kabupaten' => $kabupaten,
        ]);
    }

    /**
     * GET /api/v1/public/tentang-daerah/lambang
     */
    public function lambang(Request $request): JsonResponse
    {
        $section = Schema::hasTable('about_section_contents') ? AboutSectionContent::query()
            ->where('section_key', 'lambang_daerah')
            ->where('status_publikasi', 'published')
            ->first(['judul', 'subjudul', 'konten']) : null;
        if (! $section && Schema::hasTable('portal_contents')) {
            $item = PortalContent::query()->where('type', 'page')->where('slug', 'lambang-daerah')->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now())->first(['title', 'summary', 'body']);
            $section = $item ? ['judul' => $item->title, 'subjudul' => $item->summary, 'konten' => $item->body] : null;
        }

        return response()->json(['data' => $section ?: [
            'judul' => 'Lambang dan Moto Kabupaten Kepulauan Sangihe',
            'subjudul' => 'Filosofi unsur dan warna lambang daerah',
            'konten' => 'Lambang daerah memuat unsur alam, kehidupan, dan cita-cita masyarakat Sangihe.',
            'unsur' => [
                ['nama' => 'Padi', 'makna' => 'Pangan dan kemakmuran.'],
                ['nama' => 'Kapas', 'makna' => 'Sandang dan kesejahteraan.'],
                ['nama' => 'Senjata tradisional', 'makna' => 'Keberanian, keteguhan, dan warisan budaya.'],
                ['nama' => 'Gunung', 'makna' => 'Bentang alam serta sumber daya daerah.'],
                ['nama' => 'Sungai', 'makna' => 'Sumber kehidupan dan air.'],
                ['nama' => 'Petak sawah', 'makna' => 'Pertanian dan ketahanan pangan.'],
                ['nama' => 'Bendungan', 'makna' => 'Pembangunan dan pengelolaan air.'],
            ],
            'warna' => [
                ['nama' => 'Hijau', 'makna' => 'Kesuburan dan kemakmuran.'],
                ['nama' => 'Biru', 'makna' => 'Ketenangan dan kedamaian.'],
                ['nama' => 'Kuning', 'makna' => 'Keagungan, keluhuran, dan kekayaan.'],
                ['nama' => 'Hitam', 'makna' => 'Keteguhan dan keabadian.'],
                ['nama' => 'Merah', 'makna' => 'Keberanian.'],
                ['nama' => 'Putih', 'makna' => 'Kesucian dan kejujuran.'],
            ],
        ]]);
    }
}
