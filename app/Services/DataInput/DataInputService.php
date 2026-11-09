<?php

namespace App\Services\DataInput;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Models\Keluarga;
use App\Services\Authorization\WewenangService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * SPRINT 6: DataInput Service - Control input data based on authority
 *
 * SERVICE LAYER: Arsitektur §7.2
 * Service untuk mengontrol siapa yang boleh input data apa dan di mana.
 * Before: validation scattered di controller dan resource.
 * After: satu service terpusat untuk semua validasi input data.
 */
class DataInputService
{
    private WewenangService $wewenang;

    public function __construct(WewenangService $wewenang)
    {
        $this->wewenang = $wewenang;
    }

    /**
     * Validasi dan simpan data Rumah.
     */
    public function storeRumah(array $data): Rumah
    {
        $this->assertCanCreateRumah($data);

        $validated = $this->validateRumah($data);

        $validated['created_by'] = $this->wewenang->getUser()->id;

        return Rumah::query()->create($validated);
    }

    /**
     * Update data Rumah.
     */
    public function updateRumah(Rumah $rumah, array $data): Rumah
    {
        $this->assertCanEditRumah($rumah);

        $validated = $this->validateRumah($data, $rumah->id);

        $rumah->update($validated);

        return $rumah->fresh();
    }

    /**
     * Hapus data Rumah.
     */
    public function deleteRumah(Rumah $rumah): bool
    {
        $this->assertCanDeleteRumah($rumah);

        return $rumah->delete();
    }

    /**
     * Validasi dan simpan data Keluarga.
     */
    public function storeKeluarga(array $data): Keluarga
    {
        $this->assertCanCreateKeluarga($data);

        $validated = $this->validateKeluarga($data);

        $validated['created_by'] = $this->wewenang->getUser()->id;

        return Keluarga::query()->create($validated);
    }

    /**
     * Update data Keluarga.
     */
    public function updateKeluarga(Keluarga $keluarga, array $data): Keluarga
    {
        $this->assertCanEditKeluarga($keluarga);

        $validated = $this->validateKeluarga($data, $keluarga->id);

        $keluarga->update($validated);

        return $keluarga->fresh();
    }

    /**
     * Hapus data Keluarga.
     */
    public function deleteKeluarga(Keluarga $keluarga): bool
    {
        $this->assertCanDeleteKeluarga($keluarga);

        return $keluarga->delete();
    }

    /**
     * Validasi dan simpan data Penduduk.
     */
    public function storePenduduk(array $data): Penduduk
    {
        $this->assertCanCreatePenduduk($data);

        $this->assertNikUnique($data);

        $validated = $this->validatePenduduk($data);

        $validated['created_by'] = $this->wewenang->getUser()->id;

        return Penduduk::query()->create($validated);
    }

    /**
     * Update data Penduduk.
     */
    public function updatePenduduk(Penduduk $penduduk, array $data): Penduduk
    {
        $this->assertCanEditPenduduk($penduduk);

        $this->assertNikUnique($data, $penduduk->id);

        $validated = $this->validatePenduduk($data, $penduduk->id);

        $penduduk->update($validated);

        return $penduduk->fresh();
    }

    /**
     * Hapus data Penduduk.
     */
    public function deletePenduduk(Penduduk $penduduk): bool
    {
        $this->assertCanDeletePenduduk($penduduk);

        return $penduduk->delete();
    }

    // ========================
    // Validation Rules
    // ========================

    public function validateRumah(array $data, ?int $ignoreId = null): array
    {
        $rules = [
            'desa_id' => ['required', 'integer', 'exists:desas,id'],
            'dusun_id' => ['nullable', 'integer', 'exists:dusuns,id'],
            'kode_rumah' => ['required', 'string', 'max:255', 'unique:rumah,kode_rumah,' . ($ignoreId ?? 'NULL')],
            'alamat_lengkap' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status_rumah' => ['required', 'in:' . implode(',', array_keys(Rumah::STATUS))],
            'keterangan' => ['nullable', 'string'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function validateKeluarga(array $data, ?int $ignoreId = null): array
    {
        $rules = [
            'rumah_id' => ['required', 'integer', 'exists:rumah,id'],
            'nomor_kk' => ['required', 'string', 'max:32', 'unique:keluarga,nomor_kk,' . ($ignoreId ?? 'NULL')],
            'nama_kepala_keluarga' => ['required', 'string', 'max:255'],
            'jumlah_anggota' => ['required', 'integer', 'min:0'],
            'status_ekonomi' => ['required', 'in:' . implode(',', array_keys(Keluarga::STATUS_EKONOMI))],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function validatePenduduk(array $data, ?int $ignoreId = null): array
    {
        $rules = [
            'keluarga_id' => ['required', 'integer', 'exists:keluarga,id'],
            'nik' => ['required', 'string', 'max:32', 'unique:penduduk,nik,' . ($ignoreId ?? 'NULL')],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:' . implode(',', array_keys(Penduduk::JENIS_KELAMIN))],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:' . implode(',', array_keys(Penduduk::AGAMA))],
            'status_perkawinan' => ['required', 'in:' . implode(',', array_keys(Penduduk::STATUS_PERKAWINAN))],
            'pendidikan' => ['required', 'in:' . implode(',', array_keys(Penduduk::PENDIDIKAN))],
            'pekerjaan' => ['nullable', 'string', 'max:255'],
            'penghasilan' => ['nullable', 'numeric', 'min:0'],
            'status_dalam_keluarga' => ['required', 'in:' . implode(',', array_keys(Penduduk::STATUS_DALAM_KELUARGA))],
            'nomor_hp' => ['nullable', 'string', 'max:32'],
            'status_penduduk' => ['required', 'in:' . implode(',', array_keys(Penduduk::STATUS_PENDUDUK))],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    // ========================
    // Authorization Assertions
    // ========================

    public function assertCanCreateRumah(array $data): void
    {
        $desaId = $data['desa_id'] ?? null;
        $desa = $desaId ? Desa::query()->find($desaId) : null;
        $kecamatanId = $desa ? $desa->kecamatan_id ?? 0 : 0;

        if (! $this->wewenang->canCreateInRegion($kecamatanId, $desaId)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk membuat data rumah di wilayah ini.');
        }

        if ($this->wewenang->isDesaLevel() && $desaId !== $this->wewenang->getUser()->desa_id) {
            throw new AuthorizationException('Admin Desa hanya dapat membuat data rumah di desanya sendiri.');
        }
    }

    public function assertCanEditRumah(Rumah $rumah): void
    {
        if (! $this->wewenang->canEditRecord(Rumah::class, $rumah->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk mengedit data rumah ini.');
        }

        if ($rumah->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data rumah yang sudah disetujui tidak dapat diedit.');
        }
    }

    public function assertCanDeleteRumah(Rumah $rumah): void
    {
        if (! $this->wewenang->canDeleteRecord(Rumah::class, $rumah->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk menghapus data rumah ini.');
        }

        if ($rumah->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data rumah yang sudah disetujui tidak dapat dihapus.');
        }
    }

    public function assertCanCreateKeluarga(array $data): void
    {
        $rumahId = $data['rumah_id'] ?? null;
        $rumah = $rumahId ? Rumah::query()->find($rumahId) : null;

        if (! $rumah) {
            throw new AuthorizationException('Rumah tidak ditemukan.');
        }

        $kecamatanId = $rumah->desa?->kecamatan_id ?? 0;

        if (! $this->wewenang->canCreateInRegion($kecamatanId, $rumah->desa_id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk membuat data keluarga di wilayah ini.');
        }

        if ($this->wewenang->isDesaLevel() && $rumah->desa_id !== $this->wewenang->getUser()->desa_id) {
            throw new AuthorizationException('Admin Desa hanya dapat membuat data keluarga di desanya sendiri.');
        }
    }

    public function assertCanEditKeluarga(Keluarga $keluarga): void
    {
        if (! $this->wewenang->canEditRecord(Keluarga::class, $keluarga->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk mengedit data keluarga ini.');
        }

        if ($keluarga->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data keluarga yang sudah disetujui tidak dapat diedit.');
        }
    }

    public function assertCanDeleteKeluarga(Keluarga $keluarga): void
    {
        if (! $this->wewenang->canDeleteRecord(Keluarga::class, $keluarga->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk menghapus data keluarga ini.');
        }

        if ($keluarga->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data keluarga yang sudah disetujui tidak dapat dihapus.');
        }
    }

    public function assertCanCreatePenduduk(array $data): void
    {
        $keluargaId = $data['keluarga_id'] ?? null;
        $keluarga = $keluargaId ? Keluarga::query()->with('rumah')->find($keluargaId) : null;

        if (! $keluarga) {
            throw new AuthorizationException('Keluarga tidak ditemukan.');
        }

        $kecamatanId = $keluarga->rumah?->desa?->kecamatan_id ?? 0;

        if (! $this->wewenang->canCreateInRegion($kecamatanId, $keluarga->rumah?->desa_id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk membuat data penduduk di wilayah ini.');
        }

        if ($this->wewenang->isDesaLevel() && $keluarga->rumah?->desa_id !== $this->wewenang->getUser()->desa_id) {
            throw new AuthorizationException('Admin Desa hanya dapat membuat data penduduk di desanya sendiri.');
        }
    }

    public function assertCanEditPenduduk(Penduduk $penduduk): void
    {
        if (! $this->wewenang->canEditRecord(Penduduk::class, $penduduk->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk mengedit data penduduk ini.');
        }

        if ($penduduk->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data penduduk yang sudah disetujui tidak dapat diedit.');
        }
    }

    public function assertCanDeletePenduduk(Penduduk $penduduk): void
    {
        if (! $this->wewenang->canDeleteRecord(Penduduk::class, $penduduk->id)) {
            throw new AuthorizationException('Anda tidak memiliki wewenang untuk menghapus data penduduk ini.');
        }

        if ($penduduk->hasApprovedSubmissionBatch()) {
            throw new AuthorizationException('Data penduduk yang sudah disetujui tidak dapat dihapus.');
        }
    }

    // ========================
    // Data Integrity
    // ========================

    public function assertNikUnique(array $data, ?int $ignoreId = null): void
    {
        $nik = $data['nik'] ?? null;
        if (! $nik) {
            return;
        }

        $user = $this->wewenang->getUser();

        // Untuk Admin Desa - cek uniqueness per kecamatan
        if ($user->isAdminDesa()) {
            $kecamatanId = $user->kecamatan_id;

            $exists = Penduduk::query()
                ->where('nik', $nik)
                ->whereHas('keluarga.rumah.desa', fn ($q) => $q->where('kecamatan_id', $kecamatanId))
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK sudah terdaftar di wilayah kecamatan ini.',
                ]);
            }
        }
    }
}
