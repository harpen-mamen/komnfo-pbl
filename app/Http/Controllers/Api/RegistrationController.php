<?php

namespace App\Http\Controllers\Api;

use Cache;
use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\User;
use App\Models\UserRegistration;
use App\Models\Wilayah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public const AVAILABLE_ROLES = UserRegistration::AVAILABLE_ROLES;

    /**
     * GET /api/v1/registration/form-data
     * Return roles, kecamatan, opd options for the registration form.
     */
    public function formData(): JsonResponse
    {
        $roles = self::AVAILABLE_ROLES;
        $kecamatans = Wilayah::query()->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->orderBy('nama')->pluck('nama', 'id');
        $desas = Wilayah::query()->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])->where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'parent_id']);
        $opds = Opd::query()->where('aktif', true)->orderBy('nama')->pluck('nama', 'id');

        return response()->json([
            'roles' => $roles,
            'kecamatans' => $kecamatans,
            'desas' => $desas,
            'opds' => $opds,
        ]);
    }

    /**
     * POST /api/v1/registration
     * Handle a new registration request.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'nip' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'role' => ['required', 'string', 'in:' . implode(',', array_keys(self::AVAILABLE_ROLES))],
            'kecamatan_id' => ['nullable', 'exists:wilayahs,id'],
            'desa_id' => ['nullable', 'exists:wilayahs,id'],
            'opd_id' => ['nullable', 'exists:opds,id'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $data = $validator->getData();
            $role = $data['role'] ?? null;

            if ($role === 'admin_kecamatan' && empty($data['kecamatan_id'])) {
                $validator->errors()->add('kecamatan_id', 'Kecamatan wajib dipilih untuk Admin Kecamatan.');
            }
            if ($role === 'admin_desa') {
                if (empty($data['kecamatan_id'])) {
                    $validator->errors()->add('kecamatan_id', 'Kecamatan wajib dipilih untuk Admin Desa.');
                }
                if (empty($data['desa_id'])) {
                    $validator->errors()->add('desa_id', 'Desa wajib dipilih untuk Admin Desa.');
                }
            }
            if (str_starts_with((string) $role, 'admin_') || str_starts_with((string) $role, 'operator_')) {
                $isOpdRole = ! in_array($role, ['admin_kecamatan', 'admin_desa'], true);
                if ($isOpdRole && empty($data['opd_id'])) {
                    $validator->errors()->add('opd_id', 'OPD wajib dipilih untuk role perangkat daerah.');
                }
            }
            if (! empty($data['desa_id']) && ! empty($data['kecamatan_id'])) {
                $belongs = Wilayah::query()
                    ->whereKey($data['desa_id'])
                    ->whereIn('tipe', [Wilayah::TIPE_DESA, Wilayah::TIPE_KAMPUNG])
                    ->where('aktif', true)
                    ->where('parent_id', $data['kecamatan_id'])
                    ->exists();
                if (! $belongs) {
                    $validator->errors()->add('desa_id', 'Desa tidak berada di kecamatan yang dipilih.');
                }
            }
            if (! empty($data['kecamatan_id']) && ! Wilayah::query()->whereKey($data['kecamatan_id'])->where('tipe', Wilayah::TIPE_KECAMATAN)->where('aktif', true)->exists()) {
                $validator->errors()->add('kecamatan_id', 'Kecamatan tidak aktif atau tidak ditemukan.');
            }
            if (! empty($data['opd_id']) && ! Opd::query()->whereKey($data['opd_id'])->where('aktif', true)->exists()) {
                $validator->errors()->add('opd_id', 'OPD yang dipilih tidak aktif.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (User::where('email', $data['email'])->exists()) {
            return response()->json([
                'message' => 'Email sudah terdaftar di sistem.',
                'errors' => ['email' => ['Email sudah terdaftar di sistem.']],
            ], 422);
        }

        $existingRegistration = UserRegistration::where('email', $data['email'])->first();
        if ($existingRegistration && in_array($existingRegistration->status, ['pending', 'revision'], true)) {
            return response()->json([
                'message' => 'Ada permintaan registrasi yang masih menunggu untuk email ini.',
                'errors' => ['email' => ['Permintaan sebelumnya masih menunggu verifikasi.']],
            ], 422);
        }
        if ($existingRegistration && $existingRegistration->status === UserRegistration::STATUS_APPROVED) {
            return response()->json([
                'message' => 'Registrasi untuk email ini sudah pernah disetujui.',
                'errors' => ['email' => ['Registrasi sudah disetujui sebelumnya.']],
            ], 422);
        }

        $registrationData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'nip' => $data['nip'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'kecamatan_id' => $data['kecamatan_id'] ?? null,
            'desa_id' => $data['desa_id'] ?? null,
            'opd_id' => $data['opd_id'] ?? null,
            'requested_role' => $data['role'],
            'status' => UserRegistration::STATUS_PENDING,
            'admin_notes' => null,
            'verified_by' => null,
            'verified_at' => null,
            'approved_at' => null,
        ];

        $registration = $existingRegistration
            ? tap($existingRegistration)->update($registrationData)
            : UserRegistration::create($registrationData);

        $token = Str::random(64);
        Cache::put('registration_token_' . $registration->id, $token, now()->addDays(7));

        return response()->json([
            'message' => 'Registrasi berhasil dikirim. Menunggu verifikasi administrator.',
            'data' => [
                'registration_id' => $registration->id,
                'email' => $registration->email,
                'status' => $registration->status,
            ],
        ], 201);
    }
}
