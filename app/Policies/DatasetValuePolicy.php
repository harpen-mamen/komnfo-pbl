<?php

namespace App\Policies;

use App\Models\DatasetValue;
use App\Models\User;

class DatasetValuePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->hasRole("super_admin") || $user->hasRole("admin")) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasAccess($user);
    }

    public function view(User $user, DatasetValue $record): bool
    {
        if ($this->isWalidata($user) || $this->isKecamatan($user)) return true;
        return $this->isOwnOpd($user, $record);
    }

    public function create(User $user): bool
    {
        if ($user->hasRole("super_admin") || $user->hasRole("admin")) return true;
        return (bool) $user->opd_id && ($user->isOpdAdmin() || $user->isKepalaDinas());
    }

    public function update(User $user, DatasetValue $record): bool
    {
        if ($record->status === DatasetValue::STATUS_PUBLISHED) return false;
        if ($this->isWalidata($user)) return true;
        return $this->isOwnOpd($user, $record) && in_array($record->status, ["draft", "revision"], true);
    }

    public function delete(User $user, DatasetValue $record): bool
    {
        if ($this->isWalidata($user)) return true;
        return $this->isOwnOpd($user, $record) && $record->status === "draft";
    }

    public function submit(User $user, DatasetValue $record): bool
    {
        return $this->isOwnOpd($user, $record)
            && ($user->isOpdAdmin() || $user->isKepalaDinas())
            && $record->status === "draft";
    }

    public function verify(User $user, DatasetValue $record): bool
    {
        return $this->isWalidata($user) && $record->status === "submitted";
    }

    public function publish(User $user, DatasetValue $record): bool
    {
        return $this->isWalidata($user) && $record->status === "verified";
    }

    private function hasAccess(User $user): bool
    {
        if ($user->hasAnyRole(["super_admin", "admin", "walidata", "admin_kominfo", "admin_kecamatan"])) return true;
        return (bool) $user->opd_id && ($user->isOpdAdmin() || $user->isKepalaDinas());
    }

    private function isWalidata(User $user): bool
    {
        return $user->hasAnyRole(["admin_kominfo", "walidata", "super_admin"]);
    }

    private function isKecamatan(User $user): bool
    {
        return $user->hasRole("admin_kecamatan");
    }

    private function hasOpdRole(User $user): bool
    {
        return (bool) $user->opd_id && ($user->isOpdAdmin() || $user->isKepalaDinas());
    }

    private function isOwnOpd(User $user, DatasetValue $record): bool
    {
        if (!$user->opd_id) return false;
        return $record->dataset && $record->dataset->opd_id === $user->opd_id;
    }
}
