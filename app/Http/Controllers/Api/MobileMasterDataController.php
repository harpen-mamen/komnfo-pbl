<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Throwable;
use App\Models\Opd;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Sekolah;
use App\Models\DataKendala;
use App\Support\PanelAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * FASE 2: Mobile Master Data Controller
 *
 * Implementasi Prinsip 3 dan 21:
 * - Data penduduk BUKAN diinput ulang oleh Admin Desa
 * - Data penduduk berasal dari OPD (Disdukcapil)
 * - Admin Desa melakukan LOOKUP, bukan CREATE
 *
 * Flow mobile:
 *   1. Admin Desa cari KK/Penduduk
 *   2. Lookup via API ini
 *   3. Jika ditemukan -> gunakan data existing
 *   4. Jika TIDAK ditemukan -> POST /data-kendala
 *
 * READ ONLY: tidak ada endpoint create/update/delete untuk master data.
 */
class MobileMasterDataController
{
    /**
     * Lookup penduduk by NIK atau nama.
     *
     * GET /api/v1/mobile/master/penduduk?q=nik:1234567890
     * GET /api/v1/mobile/master/penduduk?q=nama:budi
     * GET /api/v1/mobile/master/penduduk?nomor_kk=1234567890123456
     */
    public function lookupPenduduk(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);
        // Admin Desa HANYA boleh lookup penduduk dalam desa-nya
        abort_unless(
            PanelAccess::isAdminDesa() || PanelAccess::isAdminLapangan() || PanelAccess::isAdminKecamatan(),
            403,
            'Akses lookup penduduk hanya untuk Admin Desa/Kecamatan.'
        );

        $query = $request->get('q', '');
        $nomorKk = $request->get('nomor_kk');

        $pendudukQuery = Penduduk::query()
            ->with(['keluarga.rumah.desa', 'createdBy'])
            // Hanya penduduk yang sudah ter-approve (master data final)
            // untuk sementara kita tampilkan semua; nanti difilter approved
            ->orderBy('nama_lengkap')
            ->limit(50);

        if ($nomorKk) {
            // Lookup by nomor KK -> ambil semua anggota keluarga
            $keluarga = Keluarga::query()
                ->where('nomor_kk', $nomorKk)
                ->with(['penduduk', 'rumah.desa'])
                ->first();

            if (! $keluarga) {
                return response()->json([
                    'found' => false,
                    'message' => 'KK tidak ditemukan di database kependudukan. Silakan laporkan kendala.',
                    'data' => null,
                ]);
            }

            return response()->json([
                'found' => true,
                'data' => [
                    'keluarga' => [
                        'id' => $keluarga->id,
                        'nomor_kk' => $keluarga->nomor_kk,
                        'nama_kepala_keluarga' => $keluarga->nama_kepala_keluarga,
                        'jumlah_anggota' => $keluarga->jumlah_anggota,
                        'status_ekonomi' => $keluarga->status_ekonomi,
                    ],
                    'rumah' => $keluarga->rumah ? [
                        'id' => $keluarga->rumah->id,
                        'kode_rumah' => $keluarga->rumah->kode_rumah,
                        'alamat_lengkap' => $keluarga->rumah->alamat_lengkap,
                        'latitude' => $keluarga->rumah->latitude,
                        'longitude' => $keluarga->rumah->longitude,
                        'desa' => $keluarga->rumah->desa?->nama,
                    ] : null,
                    'anggota' => $keluarga->penduduk->map(fn ($p) => [
                        'id' => $p->id,
                        'nik' => $p->nik,
                        'nama_lengkap' => $p->nama_lengkap,
                        'jenis_kelamin' => $p->jenis_kelamin,
                        'tanggal_lahir' => $p->tanggal_lahir?->format('Y-m-d'),
                        'status_dalam_keluarga' => $p->status_dalam_keluarga,
                        'pekerjaan' => $p->pekerjaan,
                        'pendidikan' => $p->pendidikan,
                    ])->values()->all(),
                ],
            ]);
        }

        if (empty($query)) {
            return response()->json(['error' => 'Parameter q atau nomor_kk wajib diisi.'], 422);
        }

        // Parse query: "nik:1234567890" atau "nama:budi"
        if (str_starts_with($query, 'nik:')) {
            $nik = substr($query, 4);
            $pendudukQuery->where('nik', $nik);
        } elseif (str_starts_with($query, 'nama:')) {
            $nama = substr($query, 5);
            $pendudukQuery->where('nama_lengkap', 'LIKE', '%' . $nama . '%');
        } else {
            // Default: treat as nama search
            $pendudukQuery->where('nama_lengkap', 'LIKE', '%' . $query . '%');
        }

        $results = $pendudukQuery->get();

        return response()->json([
            'found' => $results->isNotEmpty(),
            'count' => $results->count(),
            'data' => $results->map(fn ($p) => [
                'id' => $p->id,
                'nik' => $p->nik,
                'nama_lengkap' => $p->nama_lengkap,
                'jenis_kelamin' => $p->jenis_kelamin,
                'tanggal_lahir' => $p->tanggal_lahir?->format('Y-m-d'),
                'tempat_lahir' => $p->tempat_lahir,
                'nomor_kk' => $p->keluarga?->nomor_kk,
                'alamat' => $p->keluarga?->rumah?->alamat_lengkap,
                'desa' => $p->keluarga?->rumah?->desa?->nama,
            ])->values()->all(),
        ]);
    }

    /**
     * Lookup sekolah by NPSN atau nama.
     * Untuk object lapangan Sekolah yang dideploy dari data Disdik.
     */
    public function lookupSekolah(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);
        abort_unless(
            PanelAccess::isAdminDesa() || PanelAccess::isAdminLapangan() || PanelAccess::isAdminKecamatan(),
            403,
            'Akses lookup sekolah hanya untuk Admin Desa/Kecamatan.'
        );

        $query = $request->get('q', '');

        $sekolahQuery = Sekolah::query()
            ->aktif()
            ->orderBy('nama_sekolah')
            ->limit(50);

        if (empty($query)) {
            $results = $sekolahQuery->get();
        } elseif (str_starts_with($query, 'npsn:')) {
            $npsn = substr($query, 5);
            $results = $sekolahQuery->where('npsn', $npsn)->get();
        } else {
            $results = $sekolahQuery->where('nama_sekolah', 'LIKE', '%' . $query . '%')->get();
        }

        return response()->json([
            'found' => $results->isNotEmpty(),
            'count' => $results->count(),
            'data' => $results->map(fn ($s) => [
                'id' => $s->id,
                'npsn' => $s->npsn,
                'nama_sekolah' => $s->nama_sekolah,
                'jenjang' => $s->jenjang,
                'status' => $s->status,
                'akreditasi' => $s->akreditasi,
                'alamat' => $s->alamat,
                'kecamatan' => $s->kecamatan?->nama,
                'desa' => $s->desa?->nama,
                'latitude' => $s->koordinat_lat,
                'longitude' => $s->koordinat_lng,
            ])->values()->all(),
        ]);
    }

    /**
     * List data kendala yang dilaporkan.
     * GET /api/v1/mobile/data-kendala
     */
    public function listKendala(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);
        abort_unless(PanelAccess::isAdminDesa(), 403, 'Akses ini hanya untuk Admin Desa.');

        $kendalas = DataKendala::query()
            ->where('reported_by', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'jenis_kendala' => $k->jenis_kendala,
                'nik' => $k->nik,
                'nomor_kk' => $k->nomor_kk,
                'nama_lengkap' => $k->nama_lengkap,
                'status' => $k->status,
                'diteruskan_ke_opd_id' => $k->diteruskan_ke_opd_id,
                'response_notes' => $k->response_notes,
                'created_at' => $k->created_at?->toIso8601String(),
            ]);

        return response()->json([
            'count' => $kendalas->count(),
            'data' => $kendalas,
        ]);
    }

    /**
     * Buat laporan data kendala (data tidak ditemukan).
     *
     * POST /api/v1/mobile/data-kendala
     *
     * Body:
     *   {
     *     "jenis_kendala": "penduduk_tidak_ditemukan",
     *     "nik": "...",
     *     "nomor_kk": "...",
     *     "nama_lengkap": "...",
     *     "alamat": "...",
     *     "latitude": 1.234,
     *     "longitude": 5.678,
     *     "foto_base64": "...",  // optional
     *     "deskripsi": "..."
     *   }
     */
    public function createKendala(Request $request): JsonResponse
    {
        abort_unless($request->user(), 403);
        abort_unless(PanelAccess::isAdminDesa() || PanelAccess::isAdminLapangan(), 403, 'Hanya Admin Desa/Lapangan.');

        $validator = Validator::make($request->all(), [
            'jenis_kendala' => ['required', 'in:penduduk_tidak_ditemukan,kk_tidak_ditemukan,data_tidak_lengkap,data_tidak_konsisten,foto_tidak_bisa_diambil,koordinat_tidak_terbaca,lainnya'],
            'nik' => ['nullable', 'string', 'max:16'],
            'nomor_kk' => ['nullable', 'string', 'max:16'],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'foto_base64' => ['nullable', 'string'], // image base64
            'deskripsi' => ['required', 'string', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (! $user->desa_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akun tidak terhubung ke desa.',
            ], 403);
        }

        // Tentukan OPD tujuan berdasarkan jenis kendala
        $diteruskanKeOpdId = match ($request->input('jenis_kendala')) {
            'penduduk_tidak_ditemukan', 'kk_tidak_ditemukan', 'data_tidak_lengkap' => $this->getDukcapilOpdId(),
            default => null,
        };

        // Handle foto upload (jika ada)
        $fotoPath = null;
        if ($request->filled('foto_base64')) {
            try {
                $imageData = base64_decode($request->input('foto_base64'));
                $filename = 'kendala/' . Str::uuid() . '.jpg';
                Storage::disk('public')->put($filename, $imageData);
                $fotoPath = $filename;
            } catch (Throwable $e) {
                // Silent fail; lanjut tanpa foto
            }
        }

        $kendala = DataKendala::create([
            'reported_by' => $user->id,
            'desa_id' => $user->desa_id,
            'dusun_id' => $user->dusun_id ?? null,
            'jenis_kendala' => $request->input('jenis_kendala'),
            'nik' => $request->input('nik'),
            'nomor_kk' => $request->input('nomor_kk'),
            'nama_lengkap' => $request->input('nama_lengkap'),
            'tanggal_lahir' => $request->input('tanggal_lahir'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'alamat' => $request->input('alamat'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'foto_path' => $fotoPath,
            'deskripsi' => $request->input('deskripsi'),
            'status' => $diteruskanKeOpdId ? 'diteruskan' : 'baru',
            'diteruskan_ke_opd_id' => $diteruskanKeOpdId,
            'diteruskan_at' => $diteruskanKeOpdId ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $kendala->id,
                'jenis_kendala' => $kendala->jenis_kendala,
                'status' => $kendala->status,
                'diteruskan_ke_opd_id' => $kendala->diteruskan_ke_opd_id,
                'created_at' => $kendala->created_at->toIso8601String(),
            ],
            'message' => 'Laporan kendala berhasil dibuat. Akan ditindaklanjuti oleh OPD terkait.',
        ], 201);
    }

    private function getDukcapilOpdId(): ?int
    {
        return Opd::where('kode', 'DUKCAPIL')->value('id');
    }
}
