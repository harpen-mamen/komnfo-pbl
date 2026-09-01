<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MobileAuthController
{
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

        if (! $user->isAdminDesa() || ! $user->desa_id) {
            return response()->json(['message' => 'SIPDT Mobile hanya dapat digunakan oleh Admin Desa yang memiliki desa.'], 403);
        }

        $token = $user->createToken('sipdt-mobile-'.$credentials['device_id'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->profile($user),
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
        ];
    }
}
