<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserRegistrationApprovalService
{
    public function approve(UserRegistration $registration, ?User $verifier = null): User
    {
        $verifier ??= auth()->user();

        return DB::transaction(function () use ($registration, $verifier): User {
            $locked = UserRegistration::query()->lockForUpdate()->findOrFail($registration->id);

            if (! in_array($locked->status, [UserRegistration::STATUS_PENDING, UserRegistration::STATUS_REVISION], true)) {
                throw ValidationException::withMessages([
                    'registration' => 'Registrasi ini sudah diproses dan tidak dapat disetujui lagi.',
                ]);
            }

            if (User::query()->where('email', $locked->email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Email registrasi sudah digunakan oleh akun lain.',
                ]);
            }

            if (! Role::query()->where('name', $locked->requested_role)->exists()) {
                throw ValidationException::withMessages([
                    'requested_role' => 'Role yang diminta belum tersedia di sistem.',
                ]);
            }

            $user = User::create([
                'name' => $locked->name,
                'email' => $locked->email,
                'password' => $locked->password,
                'nip' => $locked->nip,
                'no_hp' => $locked->no_hp,
                'jabatan' => $locked->jabatan,
                'kecamatan_id' => $locked->kecamatan_id,
                'desa_id' => $locked->desa_id,
                'opd_id' => $locked->opd_id,
            ]);

            $user->assignRole($locked->requested_role);

            $locked->update([
                'status' => UserRegistration::STATUS_APPROVED,
                'verified_by' => $verifier?->id,
                'verified_at' => now(),
                'approved_at' => now(),
            ]);

            return $user;
        });
    }

    public function reject(UserRegistration $registration, string $notes, ?User $verifier = null): UserRegistration
    {
        return $this->updateDecision($registration, UserRegistration::STATUS_REJECTED, $notes, $verifier);
    }

    public function requestRevision(UserRegistration $registration, string $notes, ?User $verifier = null): UserRegistration
    {
        return $this->updateDecision($registration, UserRegistration::STATUS_REVISION, $notes, $verifier);
    }

    private function updateDecision(UserRegistration $registration, string $status, string $notes, ?User $verifier): UserRegistration
    {
        $verifier ??= auth()->user();
        $notes = trim($notes);

        if ($notes === '') {
            throw ValidationException::withMessages(['admin_notes' => 'Catatan wajib diisi.']);
        }

        if (! in_array($registration->status, [UserRegistration::STATUS_PENDING, UserRegistration::STATUS_REVISION], true)) {
            throw ValidationException::withMessages(['registration' => 'Registrasi ini sudah diproses.']);
        }

        $registration->update([
            'status' => $status,
            'verified_by' => $verifier?->id,
            'verified_at' => now(),
            'admin_notes' => $notes,
        ]);

        return $registration->fresh();
    }
}
