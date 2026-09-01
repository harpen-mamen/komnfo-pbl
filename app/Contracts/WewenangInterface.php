<?php

namespace App\Contracts;

/**
 * Interface untuk sistem wewenang/authorization terpusat.
 * 
 * Kontrak ini mendefinisikan operasi authorization yang harus diimplementasikan
 * untuk mengontrol akses data berdasarkan role dan wilayah.
 */
interface WewenangInterface
{
    /**
     * Cek apakah user memiliki wewenang untuk mengakses modul tertentu.
     */
    public function canAccessModule(string $module): bool;

    /**
     * Cek apakah user memiliki wewenang untuk membuat data di wilayah tertentu.
     */
    public function canCreateInRegion(int $kecamatanId, ?int $desaId = null): bool;

    /**
     * Cek apakah user memiliki wewenang untuk mengedit record tertentu.
     */
    public function canEditRecord(string $modelClass, int $recordId): bool;

    /**
     * Cek apakah user memiliki wewenang untuk menghapus record tertentu.
     */
    public function canDeleteRecord(string $modelClass, int $recordId): bool;

    /**
     * Cek apakah user memiliki wewenang untuk mengirim (submit) data.
     */
    public function canSubmitData(): bool;

    /**
     * Cek apakah user memiliki wewenang untuk memverifikasi data.
     */
    public function canVerifyData(): bool;

    /**
     * Cek apakah user memiliki wewenang untuk menyetujui data.
     */
    public function canApproveData(): bool;

    /**
     * Cek apakah user memiliki wewenang untuk menolak data.
     */
    public function canRejectData(): bool;

    /**
     * Dapatkan daftar modul yang dapat diakses oleh user.
     */
    public function getAccessibleModules(): array;

    /**
     * Dapatkan daftar kecamatan yang dapat diakses oleh user.
     */
    public function getAccessibleKecamatanIds(): array;

    /**
     * Dapatkan daftar desa yang dapat diakses oleh user.
     */
    public function getAccessibleDesaIds(): array;

    /**
     * Cek apakah user adalah level kabupaten.
     */
    public function isKabupatenLevel(): bool;

    /**
     * Cek apakah user adalah level kecamatan.
     */
    public function isKecamatanLevel(): bool;

    /**
     * Cek apakah user adalah level desa.
     */
    public function isDesaLevel(): bool;

    /**
     * Cek apakah user adalah level OPD.
     */
    public function isOpdLevel(): bool;

    /**
     * Dapatkan modul yang diizinkan untuk role user saat ini.
     */
    public function getAllowedModulesForCurrentRole(): array;
}
