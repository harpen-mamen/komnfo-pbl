<?php

namespace App\Http\Controllers\Api;

use App\Models\DeviceAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController
{
    /**
     * Login email + password (untuk admin_opd, dll yang masih butuh password).
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:120'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Email atau password tidak valid.'], 422);
        }

        if (! $this->canUseMobile($user)) {
            return response()->json(['message' => 'Akun ini tidak diizinkan login via mobile.'], 403);
        }

        $token = $user->createToken('sipdt-mobile-'.$credentials['device_id'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->profile($user),
        ]);
    }

    /**
     * AUTO-LOGIN untuk admin lapangan - TANPA LOGIN PAGE.
     *
     * Alur:
     *   1. Mobile app langsung panggil endpoint ini dengan device_id
     *   2. Server cek: ada device_id ini terdaftar ke user admin_lapangan/admin_desa?
     *   3. Ya -> return token
     *   4. Tidak -> 404 "Device belum diaktivasi. Hubungi Admin OPD"
     *
     * Aktivasi device dilakukan admin OPD via web panel
     * (lihat DeviceAssignmentResource di Filament).
     */
    public function autoLogin(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'device_id' => ['required', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'in:android,ios,other'],
        ]);

        $assignment = DeviceAssignment::with('user.roles', 'user.kecamatan', 'user.desa', 'user.opd')
            ->where('device_id', $payload['device_id'])
            ->where('is_active', true)
            ->first();

        if (! $assignment) {
            return response()->json([
                'message' => 'Device belum diaktivasi. Hubungi Admin OPD kecamatan Anda untuk mendaftarkan device ini.',
                'code' => 'DEVICE_NOT_REGISTERED',
            ], 404);
        }

        $user = $assignment->user;

        if (! $user || ! $user->hasAnyRole(['admin_lapangan', 'admin_desa'])) {
            return response()->json([
                'message' => 'Device ini tidak terhubung ke akun admin lapangan yang valid.',
                'code' => 'USER_INVALID',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun Anda tidak aktif. Hubungi Admin OPD.',
                'code' => 'USER_INACTIVE',
            ], 403);
        }

        // Update last seen
        $assignment->update(['last_seen_at' => now()]);
        if (! empty($payload['platform'])) {
            $assignment->update(['platform' => $payload['platform']]);
        }

        $tokenName = 'sipdt-auto-'.substr($payload['device_id'], 0, 8);
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->profile($user),
            'device' => [
                'label' => $assignment->device_label,
                'activated_at' => $assignment->activated_at,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->profile($request->user())]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message' => 'Logout berhasil.']);
    }

    private function canUseMobile(User $user): bool
    {
        return $user->hasAnyRole(['admin_desa', 'admin_lapangan'])
            && ($user->desa_id || $user->kecamatan_id);
    }

    private function profile(User $user): array
    {
        $user->loadMissing('roles', 'kecamatan', 'desa', 'opd');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'role_label' => $user->role_label,
            'roles' => $user->roles->pluck('name')->values()->all(),
            'kecamatan_id' => $user->kecamatan_id,
            'kecamatan' => $user->kecamatan?->nama,
            'desa_id' => $user->desa_id,
            'desa' => $user->desa?->nama,
            'opd_id' => $user->opd_id,
            'opd' => $user->opd?->nama,
            'auto_login' => true,
        ];
    }
}
