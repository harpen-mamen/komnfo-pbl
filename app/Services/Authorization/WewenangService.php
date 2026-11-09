<?php

namespace App\Services\Authorization;

use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rumah;
use App\Contracts\WewenangInterface;
use App\Enums\AppRole;
use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WewenangService implements WewenangInterface
{
    private ?User $user = null;

    public const MODULE_RUMAH = 'rumah';
    public const MODULE_KELUARGA = 'keluarga';
    public const MODULE_PENDUDUK = 'penduduk';
    public const MODULE_KESEHATAN = 'kesehatan';
    public const MODULE_PENDIDIKAN = 'pendidikan';

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isKabupatenLevel(): bool
    {
        if (!$this->user) {
            return false;
        }
        $roleName = $this->user->roles->first()?->name;
        $role = AppRole::tryFrom($roleName ?? '');
        return $role?->isKabupatenLevel() ?? false;
    }

    public function isKecamatanLevel(): bool
    {
        if (!$this->user) {
            return false;
        }
        $roleName = $this->user->roles->first()?->name;
        $role = AppRole::tryFrom($roleName ?? '');
        return $role?->isKecamatanLevel() ?? false;
    }

    public function isDesaLevel(): bool
    {
        if (!$this->user) {
            return false;
        }
        $roleName = $this->user->roles->first()?->name;
        $role = AppRole::tryFrom($roleName ?? '');
        return $role?->isDesaLevel() ?? false;
    }

    public function isOpdLevel(): bool
    {
        if (!$this->user) {
            return false;
        }
        $roleName = $this->user->roles->first()?->name;
        $role = AppRole::tryFrom($roleName ?? '');
        return $role?->isOpdLevel() ?? false;
    }

    public function canAccessModule(string $module): bool
    {
        return in_array($module, $this->getAccessibleModules(), true);
    }

    public function getAccessibleModules(): array
    {
        return $this->getAllowedModulesForCurrentRole();
    }

    public function getAllowedModulesForCurrentRole(): array
    {
        if (!$this->user) {
            return [];
        }

        if ($this->isKabupatenLevel()) {
            return [
                self::MODULE_RUMAH,
                self::MODULE_KELUARGA,
                self::MODULE_PENDUDUK,
                self::MODULE_KESEHATAN,
                self::MODULE_PENDIDIKAN,
            ];
        }

        $roleName = $this->user->roles->first()?->name;

        $roleModules = [
            AppRole::ADMIN_KECAMATAN->value => [self::MODULE_RUMAH],
            AppRole::ADMIN_DESA->value => [self::MODULE_RUMAH],
            AppRole::ADMIN_LAPANGAN->value => [self::MODULE_RUMAH],
            AppRole::ADMIN_DINKES->value => [self::MODULE_KESEHATAN],
            AppRole::OPERATOR_DINKES->value => [self::MODULE_KESEHATAN],
            AppRole::ADMIN_DISPEND->value => [self::MODULE_PENDIDIKAN],
            AppRole::OPERATOR_DISPEND->value => [self::MODULE_PENDIDIKAN],
        ];

        return $roleModules[$roleName] ?? [];
    }

    public function getAccessibleKecamatanIds(): array
    {
        if (!$this->user) {
            return [];
        }

        if ($this->isKabupatenLevel()) {
            return Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->pluck('id')->all();
        }

        $kecamatanId = $this->user->kecamatan_id ?? $this->user->desa?->parent_id;
        return $kecamatanId ? [$kecamatanId] : [];
    }

    public function getAccessibleDesaIds(): array
    {
        if (!$this->user) {
            return [];
        }

        if ($this->isKabupatenLevel()) {
            return Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->pluck('id')->all();
        }

        if ($this->isDesaLevel() && $this->user->desa_id) {
            return [$this->user->desa_id];
        }

        $kecamatanId = $this->user->kecamatan_id;
        if ($kecamatanId) {
            return Wilayah::query()->where('parent_id', $kecamatanId)->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->pluck('id')->all();
        }

        return [];
    }

    public function canCreateInRegion(int $kecamatanId, ?int $desaId = null): bool
    {
        if (!$this->user) {
            return false;
        }

        if ($this->isKabupatenLevel()) {
            return true;
        }

        $userKecamatanId = $this->user->kecamatan_id ?? $this->user->desa?->parent_id;
        if ((int) $userKecamatanId !== $kecamatanId) {
            return false;
        }

        if ($this->isDesaLevel()) {
            if ($desaId !== null && (int) $this->user->desa_id !== $desaId) {
                return false;
            }
        }

        return true;
    }

    public function canEditRecord(string $modelClass, int $recordId): bool
    {
        $model = $modelClass::query()->find($recordId);
        if (!$model) {
            return false;
        }

        if ($this->isKabupatenLevel()) {
            return true;
        }

        if ($model instanceof Keluarga || $model instanceof Penduduk) {
            if ($this->isKependudukanDataOwner()) {
                return ! $model->hasApprovedSubmissionBatch();
            }
        }

        return match (true) {
            $model instanceof Rumah => $this->canEditRumah($model),
            $model instanceof Keluarga => $this->canEditKeluarga($model),
            $model instanceof Penduduk => $this->canEditPenduduk($model),
            // DataKesehatanBulanan and DataPendidikanBulanan tables removed
            default => $this->canEditOwnRecord($model),
        };
    }

    public function canDeleteRecord(string $modelClass, int $recordId): bool
    {
        $model = $modelClass::query()->find($recordId);
        if (!$model) {
            return false;
        }

        if (method_exists($model, 'hasApprovedSubmissionBatch') && $model->hasApprovedSubmissionBatch()) {
            return false;
        }

        if ($this->isKabupatenLevel()) {
            return true;
        }
        if (($model instanceof Keluarga || $model instanceof Penduduk) && $this->isKependudukanDataOwner()) {
            return true;
        }
        if ($this->isKecamatanLevel()) {
            return false;
        }
        if ($this->isDesaLevel()) {
            return $this->isOwner($model);
        }

        return false;
    }

    public function canSubmitData(): bool
    {
        if (!$this->user) {
            return false;
        }

        return $this->user->hasAnyRole([
            AppRole::ADMIN_KECAMATAN->value,
            AppRole::ADMIN_DESA->value,
            AppRole::ADMIN_KOMINFO->value,
            AppRole::ADMIN->value,
            AppRole::SUPER_ADMIN->value,
        ]);
    }

    public function canVerifyData(): bool
    {
        if (!$this->user) {
            return false;
        }

        if ($this->user->hasRole(AppRole::ADMIN_KECAMATAN->value)) {
            return true;
        }

        if ($this->user->hasAnyRole([
            AppRole::ADMIN_KOMINFO->value,
            AppRole::ADMIN->value,
            AppRole::SUPER_ADMIN->value,
        ])) {
            return true;
        }

        return false;
    }

    public function canApproveData(): bool
    {
        if (!$this->user) {
            return false;
        }

        return $this->user->hasAnyRole([
            AppRole::ADMIN_KOMINFO->value,
            AppRole::ADMIN->value,
            AppRole::SUPER_ADMIN->value,
        ]);
    }

    public function canRejectData(): bool
    {
        return $this->canApproveData();
    }

    public function isOwner(Model $model): bool
    {
        if (!$this->user) {
            return false;
        }

        if (isset($model->created_by)) {
            return (int) $model->created_by === $this->user->id;
        }

        if ($model instanceof Keluarga && $model->rumah) {
            return $this->isOwner($model->rumah);
        }

        if ($model instanceof Penduduk && $model->keluarga?->rumah) {
            return $this->isOwner($model->keluarga->rumah);
        }

        return false;
    }

    private function canEditRumah(Rumah $rumah): bool
    {
        if ($rumah->hasApprovedSubmissionBatch()) {
            return false;
        }

        if ($this->isKecamatanLevel()) {
            $userKecamatanId = $this->user->kecamatan_id;
            return $rumah->desa?->parent_id === $userKecamatanId;
        }

        if ($this->isDesaLevel()) {
            return (int) $rumah->desa_id === (int) $this->user->desa_id;
        }

        if ($this->user->isAdminLapangan()) {
            return Rumah::query()->visibleToCurrentUser()->whereKey($rumah->id)->exists();
        }

        return false;
    }

    private function isKependudukanDataOwner(): bool
    {
        if (! $this->user) {
            return false;
        }

        return $this->user->hasAnyRole([
            AppRole::ADMIN_KEPENDUDUKAN->value,
            AppRole::OPERATOR_KEPENDUDUKAN->value,
            AppRole::ADMIN_DUKCAPIL->value,
            AppRole::OPERATOR_DUKCAPIL->value,
        ]) || ($this->user->hasRole(AppRole::ADMIN_OPD->value) && $this->user->opd?->kode === 'DUKCAPIL');
    }

    private function canEditKeluarga(Keluarga $keluarga): bool
    {
        if ($keluarga->hasApprovedSubmissionBatch()) {
            return false;
        }

        if ($this->isKecamatanLevel() || $this->isDesaLevel()) {
            return $this->isOwner($keluarga) || $this->canEditRumah($keluarga->rumah);
        }

        return false;
    }

    private function canEditPenduduk(Penduduk $penduduk): bool
    {
        if ($penduduk->hasApprovedSubmissionBatch()) {
            return false;
        }

        if ($this->isKecamatanLevel() || $this->isDesaLevel()) {
            return $this->isOwner($penduduk) || $this->canEditKeluarga($penduduk->keluarga);
        }

        return false;
    }

    // Note: canEditDataKesehatan and canEditDataPendidikan removed - tables deleted
    private function canEditOwnRecord(Model $model): bool
    {
        if (isset($model->created_by)) {
            return (int) $model->created_by === $this->user->id;
        }
        return false;
    }

    public function assertCan(string $ability, ?string $message = null): void
    {
        if (!$this->$ability()) {
            throw new AuthorizationException(
                $message ?? 'Anda tidak memiliki wewenang untuk melakukan aksi ini.'
            );
        }
    }

    public function canSubmitBatch(int $kecamatanId, array $modules = []): bool
    {
        if (!$this->canSubmitData()) {
            return false;
        }

        if ($this->isDesaLevel()) {
            $allowedModules = [self::MODULE_RUMAH, self::MODULE_KELUARGA, self::MODULE_PENDUDUK];
            foreach ($modules as $module) {
                if (!in_array($module, $allowedModules, true)) {
                    return false;
                }
            }
        }

        return $this->canCreateInRegion($kecamatanId);
    }
}
