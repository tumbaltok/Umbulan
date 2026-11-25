<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\Gender;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use App\Models\User\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthApiController extends Controller
{
    /**
     * Login APK Native menggunakan Email & Password, mengembalikan Bearer Token Sanctum
     * dan mendaftarkan fcm_token perangkat jika tersedia.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
            'fcm_token' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        $email = strtolower(trim($validated['email']));
        $throttleKey = 'api-login:' . $email . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'message' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
            ], 429);
        }

        $user = User::with(['station', 'roles'])->where('email', $email)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau kata sandi tidak sesuai.',
            ], 401);
        }

        RateLimiter::clear($throttleKey);

        if (!empty($validated['fcm_token'])) {
            $this->registerDeviceToken(
                $user,
                $validated['fcm_token'],
                $validated['device_name'] ?? 'APK Native',
                $validated['platform'] ?? 'android'
            );
        }

        $tokenName = $validated['device_name'] ?? 'apk-native';
        $plainTextToken = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $plainTextToken,
                'token_type' => 'Bearer',
                'user' => $this->formatUserPayload($user),
            ],
        ]);
    }

    /**
     * Registrasi karyawan baru dari APK Native (tanpa nomor telepon/WhatsApp).
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:users,nip',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'station_id' => 'required|exists:stations,id',
            'gender_id' => 'nullable|in:1,2',
            'schedule_type' => 'required|string|in:normal,roster',
            'current_shift_choice' => 'nullable|string|in:pagi,malam,libur',
            'normal_work_days' => 'nullable|array',
            'normal_work_days.*' => 'string|max:10',
            'normal_check_in' => 'nullable|string|max:10',
            'normal_check_out' => 'nullable|string|max:10',
            'roster_start_date' => 'nullable|date',
            'device_name' => 'nullable|string|max:100',
            'fcm_token' => 'nullable|string|max:255',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        $genderId = (int) ($validated['gender_id'] ?? 1);
        $genderName = $genderId === 2 ? 'Perempuan' : 'Laki-laki';

        $rosterStartDate = $validated['roster_start_date'] ?? null;
        if ($validated['schedule_type'] === 'roster' && empty($rosterStartDate)) {
            $now = now('Asia/Jakarta');
            $tuesday = $now->copy()->startOfWeek()->addDay(); // Selasa minggu ini
            if ($now->lt($tuesday->copy()->setTime(7, 0))) {
                $tuesday->subWeek();
            }
            $shiftOffset = match ($validated['current_shift_choice'] ?? 'pagi') {
                'malam' => 1,
                'libur' => 2,
                default => 0,
            };
            $rosterStartDate = $tuesday->subWeeks($shiftOffset)->toDateString();
        }

        $user = DB::transaction(function () use ($validated, $genderId, $genderName, $rosterStartDate) {
            $defaultRole = Role::where('role_name', 'Karyawan')->first()
                ?? Role::orderBy('id')->first();

            $isNormal = $validated['schedule_type'] === 'normal';

            $created = User::create([
                'name' => trim($validated['name']),
                'nip' => !empty($validated['nip']) ? trim($validated['nip']) : null,
                'email' => strtolower(trim($validated['email'])),
                'password' => $validated['password'],
                'role_id' => $defaultRole?->id,
                'level' => 3,
                'gender' => $genderName,
                'gender_id' => $genderId,
                'station_id' => (int) $validated['station_id'],
                'schedule_type' => $validated['schedule_type'],
                'normal_work_days' => $isNormal ? ($validated['normal_work_days'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']) : null,
                'normal_check_in' => $isNormal ? ($validated['normal_check_in'] ?? '07:00:00') : null,
                'normal_check_out' => $isNormal ? ($validated['normal_check_out'] ?? '16:00:00') : null,
                'roster_start_date' => $isNormal ? null : $rosterStartDate,
            ]);

            if ($defaultRole) {
                $created->roles()->syncWithoutDetaching([$defaultRole->id => ['is_primary' => true]]);
            }

            $cutiTahunan = JenisCuti::whereNull('parent_id')
                ->whereRaw('LOWER(name_cuti) LIKE ?', ['%tahunan%'])
                ->first();

            if ($cutiTahunan) {
                SaldoCuti::firstOrCreate(
                    [
                        'user_id' => $created->id,
                        'jenis_cuti_id' => $cutiTahunan->id,
                        'tahun' => (int) date('Y'),
                    ],
                    [
                        'kuota_awal' => 12,
                        'sisa_saldo' => 12,
                    ]
                );
            }

            return $created;
        });

        $this->issueEmailVerificationOtp($user);

        if (!empty($validated['fcm_token'])) {
            $this->registerDeviceToken(
                $user,
                $validated['fcm_token'],
                $validated['device_name'] ?? 'APK Native',
                $validated['platform'] ?? 'android'
            );
        }

        $user->load(['station', 'roles']);
        $token = $user->createToken($validated['device_name'] ?? 'apk-native')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil. Kode OTP verifikasi email telah dikirim ke alamat email Anda.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $this->formatUserPayload($user),
            ],
        ], 201);
    }

    /**
     * Mengembalikan data master publik untuk kebutuhan form APK (Station, Gender, Role, Jenis Cuti).
     */
    public function masterData(): JsonResponse
    {
        $stations = Station::orderBy('name')->get([
            'id', 'name', 'type', 'kode_stasiun', 'latitude', 'longitude', 'radius_meters',
        ]);

        $jenisCutis = JenisCuti::with(['subCutis' => function ($query) {
            $query->select('id', 'parent_id', 'name_cuti', 'durasi_default', 'apakah_wajib_dokumen');
        }])
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get(['id', 'name_cuti', 'apakah_wajib_dokumen', 'butuh_surat_dokter']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'stations' => $stations,
                'genders' => Gender::all()->values(),
                'roles' => Role::orderBy('id')->get(['id', 'role_name', 'description']),
                'jenis_cutis' => $jenisCutis,
            ],
        ]);
    }

    /**
     * Mengambil profil lengkap pengguna yang sedang terautentikasi.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['station', 'roles']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $this->formatUserPayload($user),
                'devices_count' => $user->devices()->count(),
                'unread_notifications_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    /**
     * Logout APK Native: mencabut Bearer Token aktif & opsional menghapus FCM token perangkat.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        if ($request->filled('fcm_token')) {
            $user->devices()->where('fcm_token', $request->fcm_token)->delete();
        }

        $user->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Memperbarui atau mendaftarkan token FCM perangkat untuk Push Notification APK Native.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:100',
            'platform' => 'nullable|string|in:android,ios,web',
        ]);

        $device = $this->registerDeviceToken(
            $request->user(),
            $validated['fcm_token'],
            $validated['device_name'] ?? 'APK Native',
            $validated['platform'] ?? 'android'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Token FCM perangkat berhasil diperbarui.',
            'data' => [
                'device_id' => $device->id,
                'platform' => $device->platform,
                'last_active_at' => $device->last_active_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Mengirim ulang OTP verifikasi email untuk user yang sudah login via API.
     */
    public function resendEmailOtp(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email Anda sudah terverifikasi.',
            ]);
        }

        $throttleKey = 'api-verify-email-otp:' . $user->id;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'status' => 'error',
                'message' => "Terlalu banyak permintaan OTP. Tunggu {$seconds} detik.",
            ], 429);
        }

        RateLimiter::hit($throttleKey, 180);
        $this->issueEmailVerificationOtp($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Kode OTP verifikasi email telah dikirim ulang.',
        ]);
    }

    /**
     * Memverifikasi OTP 6-digit email untuk user yang sedang login via API.
     */
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'otp' => 'required|string|digits:6',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email sudah terverifikasi sebelumnya.',
                'data' => ['user' => $this->formatUserPayload($user->load(['station', 'roles']))],
            ]);
        }

        $cacheKey = "email_verify_otp_{$user->id}";
        $cached = Cache::get($cacheKey);

        if (!$cached || empty($cached['hash']) || now()->timestamp > ($cached['expires_at'] ?? 0)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP sudah kedaluwarsa atau belum diminta.',
            ], 422);
        }

        if (($cached['attempts'] ?? 0) >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Batas percobaan OTP terlampaui. Silakan minta kode baru.',
            ], 422);
        }

        if (!Hash::check($validated['otp'], $cached['hash'])) {
            $cached['attempts'] = ($cached['attempts'] ?? 0) + 1;
            Cache::put($cacheKey, $cached, now()->addMinutes(10));

            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak valid.',
            ], 422);
        }

        Cache::forget($cacheKey);
        $user->forceFill(['email_verified_at' => now()])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Email berhasil diverifikasi.',
            'data' => [
                'user' => $this->formatUserPayload($user->fresh(['station', 'roles'])),
            ],
        ]);
    }

    /**
     * Mengirim OTP reset password ke email berdasarkan Email atau NIP (stateless untuk APK Native).
     */
    public function sendResetOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity' => 'required|string|max:255',
        ]);

        $identity = trim($validated['identity']);
        $user = str_contains($identity, '@')
            ? User::where('email', strtolower($identity))->first()
            : User::where('nip', $identity)->first();

        if (!$user || empty($user->email)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Jika akun terdaftar pada sistem kami, kode OTP reset kata sandi telah dikirim ke email terdaftar.',
            ]);
        }

        $ipKey = 'api-reset-otp-ip:' . $request->ip();
        $userKey = 'api-reset-otp-user:' . $user->id;
        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($userKey, 3)) {
            $seconds = max(RateLimiter::availableIn($ipKey), RateLimiter::availableIn($userKey));
            return response()->json([
                'status' => 'error',
                'message' => "Terlalu banyak permintaan OTP. Tunggu {$seconds} detik.",
            ], 429);
        }

        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $expiryMinutes = 5;

        try {
            Mail::to($user->email)->send(new PasswordResetOtpMail($user, $otp, $expiryMinutes));
        } catch (\Throwable $e) {
            Log::warning('[API ForgotPassword] Fallback mail raw: ' . $e->getMessage());
            try {
                Mail::raw(
                    "Kode OTP Reset Kata Sandi Anda: {$otp}. Berlaku {$expiryMinutes} menit.",
                    fn ($msg) => $msg->to($user->email)->subject('OTP Reset Kata Sandi - PT META Adhya Tirta Umbulan')
                );
            } catch (\Throwable $ex) {
                Log::error('[API ForgotPassword] Gagal kirim email OTP: ' . $ex->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Layanan email sedang tidak tersedia. Silakan coba lagi nanti.',
                ], 500);
            }
        }

        RateLimiter::hit($ipKey, 300);
        RateLimiter::hit($userKey, 300);

        Cache::put("pwd_reset_otp_{$user->id}", [
            'hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes($expiryMinutes)->timestamp,
            'attempts' => 0,
            'channel' => 'email',
        ], now()->addMinutes(10));

        return response()->json([
            'status' => 'success',
            'message' => 'Jika akun terdaftar pada sistem kami, kode OTP reset kata sandi telah dikirim ke email terdaftar.',
        ]);
    }

    /**
     * Verifikasi OTP dan perbarui kata sandi akun via APK Native.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identity' => 'required|string|max:255',
            'otp' => 'required|string|digits:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $identity = trim($validated['identity']);
        $user = str_contains($identity, '@')
            ? User::where('email', strtolower($identity))->first()
            : User::where('nip', $identity)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identitas atau kode OTP tidak valid.',
            ], 422);
        }

        $cacheKey = "pwd_reset_otp_{$user->id}";
        $cached = Cache::get($cacheKey);

        if (!$cached || empty($cached['hash']) || now()->timestamp > ($cached['expires_at'] ?? 0)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak ditemukan atau telah kedaluwarsa.',
            ], 422);
        }

        if (($cached['attempts'] ?? 0) >= 5) {
            Cache::forget($cacheKey);
            return response()->json([
                'status' => 'error',
                'message' => 'Batas percobaan OTP terlampaui. Silakan minta OTP baru.',
            ], 422);
        }

        if (!Hash::check($validated['otp'], $cached['hash'])) {
            $cached['attempts'] = ($cached['attempts'] ?? 0) + 1;
            Cache::put($cacheKey, $cached, now()->addMinutes(10));

            return response()->json([
                'status' => 'error',
                'message' => 'Kode OTP tidak sesuai.',
            ], 422);
        }

        Cache::forget($cacheKey);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'remember_token' => Str::random(60),
        ])->save();

        // Cabut seluruh token aktif setelah password diubah demi keamanan
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Kata sandi berhasil diperbarui. Silakan login kembali.',
        ]);
    }

    private function registerDeviceToken(User $user, string $fcmToken, string $deviceName, string $platform): UserDevice
    {
        return UserDevice::updateOrCreate(
            ['fcm_token' => $fcmToken],
            [
                'user_id' => $user->id,
                'device_name' => $deviceName,
                'platform' => $platform,
                'last_active_at' => now(),
            ]
        );
    }

    private function issueEmailVerificationOtp(User $user): void
    {
        $otp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("email_verify_otp_{$user->id}", [
            'hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ], now()->addMinutes(15));

        try {
            Mail::raw(
                "Kode OTP Verifikasi Email Anda: {$otp}. Berlaku selama 10 menit.",
                fn ($msg) => $msg->to($user->email)->subject('Verifikasi Email Akun - PT META Adhya Tirta Umbulan')
            );
        } catch (\Throwable $e) {
            Log::warning("[API EmailVerify] Gagal mengirim email verifikasi ke {$user->email}: " . $e->getMessage());
        }
    }

    private function formatUserPayload(User $user): array
    {
        $hasChildRole = $user->role_id
            ? Role::where('parent_role_id', $user->role_id)->exists()
            : false;

        return [
            'id' => $user->id,
            'nip' => $user->nip,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            'gender' => $user->gender,
            'gender_id' => $user->gender_id,
            'level' => $user->level,
            'role_id' => $user->role_id,
            'roles' => $user->roles->map(fn ($r) => [
                'id' => $r->id,
                'role_name' => $r->role_name,
                'description' => $r->description,
                'is_primary' => (bool) ($r->pivot->is_primary ?? false),
            ])->values(),
            'station_id' => $user->station_id,
            'station' => $user->station ? [
                'id' => $user->station->id,
                'name' => $user->station->name,
                'type' => $user->station->type,
                'kode_stasiun' => $user->station->kode_stasiun,
                'latitude' => $user->station->latitude,
                'longitude' => $user->station->longitude,
                'radius_meters' => $user->station->radius_meters,
            ] : null,
            'schedule_type' => $user->schedule_type,
            'normal_work_days' => $user->normal_work_days,
            'normal_check_in' => $user->normal_check_in,
            'normal_check_out' => $user->normal_check_out,
            'roster_start_date' => $user->roster_start_date,
            'has_face_descriptor' => !empty($user->face_descriptor),
            'has_signature' => !empty($user->signature),
            'profile_photo' => $user->profile_photo,
            'is_approver' => $user->level <= 2 || $hasChildRole || $user->hasRole('Super Admin') || $user->hasRole('HRD'),
            'is_account_complete' => $user->isAccountComplete(),
            'completion_status' => $user->getAccountCompletionStatus(),
        ];
    }
}
