<?php

namespace App\Http\Controllers\Absen;

use App\Http\Controllers\Controller;
use App\Models\Absen\Kehadiran;
use App\Models\User\Station;
use App\Models\User\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class KehadiranController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    // [SEC-01 FIX] Menghasilkan nonce challenge biometrik berbatas waktu (3 menit) untuk mencegah replay attack
    public function getBiometricChallenge(Request $request): JsonResponse
    {
        $user = $request->user();
        $nonce = bin2hex(random_bytes(16));
        $cacheKey = "biometric_nonce_{$user->id}_{$nonce}";

        Cache::put($cacheKey, [
            'user_id'    => $user->id,
            'created_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(3)->timestamp,
        ], now()->addMinutes(3));

        return response()->json([
            'success'            => true,
            'nonce'              => $nonce,
            'expires_in_seconds' => 180,
        ]);
    }

    // Memproses absensi masuk (Clock In) karyawan
    public function checkIn(Request $request): JsonResponse|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            // Validasi data biometrik wajah terdaftar (128 nilai float)
            if (empty($user->face_descriptor) || !is_array($user->face_descriptor) || count($user->face_descriptor) !== 128) {
                $errorMsg = 'Data biometrik wajah belum terdaftar. Silakan rekam wajah di menu profil terlebih dahulu sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['face_descriptor' => [$errorMsg]],
                ], 422);
            }

            // Validasi verifikasi biometrik wajah
            if (!$request->boolean('is_face_verified', false)) {
                $errorMsg = 'Verifikasi biometrik wajah wajib berhasil sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['is_face_verified' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['is_face_verified' => [$errorMsg]],
                ], 422);
            }

            // Validasi uji keaktifan wajah (Liveness Anti-Spoofing) jika dikirimkan oleh klien
            if ($request->has('is_liveness_verified') && !$request->boolean('is_liveness_verified', false)) {
                $errorMsg = 'Verifikasi keaktifan wajah (Liveness Anti-Spoofing) wajib berhasil sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['is_liveness_verified' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['is_liveness_verified' => [$errorMsg]],
                ], 422);
            }

            // [SEC-01 FIX] Validasi Time-Based Nonce Challenge Biometrik
            $nonce = $request->input('biometric_nonce');
            if (!empty($nonce)) {
                $cachedNonce = Cache::pull("biometric_nonce_{$user->id}_{$nonce}");
                if (!$cachedNonce || now()->timestamp > ($cachedNonce['expires_at'] ?? 0)) {
                    $errorMsg = 'Sesi verifikasi biometrik kamera telah kedaluwarsa. Silakan ulangi pemindaian wajah.';
                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
            } elseif (!app()->environment('testing') && $request->has('face_descriptor')) {
                $errorMsg = 'Token sesi biometrik (nonce) kamera tidak valid atau kedaluwarsa.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            // [SEC-01 FIX] Anti-Replay: Deteksi Duplikasi Frame Selfie (Hash Deduplication)
            if ($request->hasFile('selfie_frame')) {
                $selfieFile = $request->file('selfie_frame');
                if ($selfieFile->isValid()) {
                    $selfieHash = hash_file('sha256', $selfieFile->getRealPath());
                    $lastHashKey = "last_selfie_hash_{$user->id}";
                    $lastHash = Cache::get($lastHashKey);

                    if ($lastHash && hash_equals($lastHash, $selfieHash)) {
                        $errorMsg = 'Terdeteksi frame foto statis/duplikat (Replay Attack). Presensi wajib melalui pemindaian kamera langsung.';
                        if (!$request->expectsJson() && !$request->ajax()) {
                            return back()->withErrors(['face_descriptor' => $errorMsg]);
                        }
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    Cache::put($lastHashKey, $selfieHash, now()->addHours(8));
                }
            }

            // [SEC-02 FIX] Validasi Akurasi Sinyal GPS
            $accuracy = $request->input('accuracy');
            if (is_numeric($accuracy) && (float)$accuracy > 150) {
                $errorMsg = 'Akurasi GPS terlalu rendah (' . round((float)$accuracy) . ' meter). Pastikan GPS aktif dan berada di area terbuka.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['latitude' => $errorMsg]);
                }
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            // [SEC-01 FIX] Server-Side Biometric Verification:
            // Wajibkan verifikasi face_descriptor aktual dari kamera terhadap data profil (Euclidean Distance <= 0.58)
            $liveDescriptor = $request->input('face_descriptor');
            if (is_string($liveDescriptor)) {
                $liveDescriptor = json_decode($liveDescriptor, true);
            }

            if (!empty($liveDescriptor)) {
                if (!is_array($liveDescriptor) || count($liveDescriptor) !== 128) {
                    $errorMsg = 'Data biometrik wajah dari kamera tidak valid (wajib 128 dimensi float).';
                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                        'errors'  => ['face_descriptor' => [$errorMsg]],
                    ], 422);
                }

                // Validasi bahwa seluruh elemen adalah float/numerik
                foreach ($liveDescriptor as $val) {
                    if (!is_numeric($val)) {
                        $errorMsg = 'Nilai vektor biometrik wajah harus berupa float yang valid.';
                        if (!$request->expectsJson() && !$request->ajax()) {
                            return back()->withErrors(['face_descriptor' => $errorMsg]);
                        }

                        return response()->json([
                            'success' => false,
                            'message' => $errorMsg,
                            'errors'  => ['face_descriptor' => [$errorMsg]],
                        ], 422);
                    }
                }

                $distance = $this->calculateEuclideanDistance($user->face_descriptor, $liveDescriptor);
                if ($distance > 0.58) {
                    $errorMsg = 'Verifikasi biometrik wajah server-side gagal: Wajah tidak cocok dengan profil terdaftar Anda.';
                    Log::warning("Pencocokan wajah server-side gagal untuk user ID {$user->id}. Distance: {$distance}");

                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                        'errors'  => ['face_descriptor' => [$errorMsg]],
                    ], 422);
                }
            } elseif (!app()->environment('testing')) {
                // Di luar testing, wajib menyertakan live face descriptor untuk mencegah bypass via API/curl
                $errorMsg = 'Data biometrik wajah kamera wajib disertakan saat melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['face_descriptor' => [$errorMsg]],
                ], 422);
            }

            // [SEC-10 FIX] Validasi range koordinat GPS latitude [-90,90] dan longitude [-180,180]
            $request->validate([
                'latitude'             => 'required|numeric|between:-90,90',
                'longitude'            => 'required|numeric|between:-180,180',
                'is_liveness_verified' => 'nullable|boolean',
                'face_descriptor'      => 'nullable',
                'reason'               => 'nullable|string|max:500',
                'reason_out_of_radius' => 'nullable|string|max:500',
                'evidence'             => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'bukti_alasan'         => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);

            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            // 0. Pastikan tidak ada sesi absen masuk hari sebelumnya yang belum di-checkout
            $openPrevious = Kehadiran::where('user_id', $user->id)
                ->whereDate('date', '<', $today)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->orderBy('date', 'desc')
                ->first();

            if ($openPrevious) {
                return response()->json([
                    'message' => 'Anda belum melakukan absen pulang untuk tanggal ' . $openPrevious->date->format('d/m/Y') . '. Harap lakukan absen pulang terlebih dahulu sebelum absen masuk hari ini.',
                    'requires_cross_day_checkout' => true,
                    'open_previous_date' => $openPrevious->date->format('Y-m-d'),
                ], 422);
            }

            // 0b. Pastikan karyawan tidak sedang dikunci istirahat pasca menginap (lembur) di station
            $todayExisting = Kehadiran::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            if ($todayExisting && $todayExisting->is_rest_after_overtime) {
                return response()->json([
                    'message' => 'Anda tidak dapat melakukan absen masuk hari ini karena telah memilih Menginap (Lembur) di Station dan wajib beristirahat pada hari ini.',
                    'is_rest_after_overtime' => true,
                ], 422);
            }

            // 1. Evaluasi jadwal kerja karyawan (jadwal OFF tetap boleh masuk dengan wajib keterangan alasan)
            $schedule = $this->scheduleService->getTodaySchedule($user, $today) ?? [];
            $isWorkingOnOffDay = !empty($schedule['is_day_off']);

            // 2. Evaluasi geolokasi terhadap seluruh stasiun dan Rumah Meter
            $geo = $this->evaluateGeofence((float) $request->latitude, (float) $request->longitude);
            $isInRadius = $geo['isInRadius'];
            $matchedStation = $geo['matchedStation'];
            $nearestStation = $geo['nearestStation'];
            $distanceMeters = $geo['distanceMeters'];

            // 3. Evaluasi status keterlambatan (tidak berlaku terlambat jika masuk pada jadwal OFF)
            $isLate = false;
            $scheduledInStr = $isWorkingOnOffDay ? null : ($schedule['scheduled_in'] ?? null);

            if (!$isWorkingOnOffDay && !empty($scheduledInStr) && $scheduledInStr !== '--:--') {
                try {
                    $scheduledIn = Carbon::parse($today . ' ' . $scheduledInStr, 'Asia/Jakarta');
                    if ($now->gt($scheduledIn)) {
                        $isLate = true;
                    }
                } catch (\Exception $e) {
                    $isLate = false;
                }
            }

            // 4. Validasi alasan wajib jika masuk pada jadwal OFF, terlambat, atau berada di luar radius
            $reason = trim((string) ($request->reason ?? $request->reason_out_of_radius));
            if (($isWorkingOnOffDay || !$isInRadius || $isLate) && empty($reason)) {
                $kondisi = [];
                if ($isWorkingOnOffDay) $kondisi[] = 'masuk kerja pada jadwal libur (OFF)';
                if ($isLate) $kondisi[] = 'terlambat';
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'stasiun terdekat';
                    $kondisi[] = 'berada di luar radius seluruh stasiun & Rumah Meter (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }

                return response()->json([
                    'message' => 'Harap isi keterangan alasan wajib karena Anda ' . implode(' dan ', $kondisi) . '!',
                ], 422);
            }

            // 5. Proses unggah dan watermark dokumen/foto bukti alasan
            $evidenceFile = $request->file('evidence') ?? $request->file('bukti_alasan');
            $evidencePath = null;

            if ($evidenceFile) {
                $statusWatermark = $isWorkingOnOffDay
                    ? 'MASUK HARI OFF'
                    : ($isLate ? 'TERLAMBAT' : 'TEPAT WAKTU');
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'Stasiun';
                    $statusWatermark .= ' | LUAR RADIUS (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }
                $evidencePath = $this->processAndWatermarkEvidence($evidenceFile, 'checkin', $user, $statusWatermark, $now);
            }

            $shiftType = $isWorkingOnOffDay
                ? 'Masuk Hari OFF'
                : ($schedule['shift_type'] ?? ($schedule['shift_name'] ?? 'Normal'));
            $scheduledOutStr = $isWorkingOnOffDay ? null : ($schedule['scheduled_out'] ?? null);
            $isFaceVerified = true;

            // [SEC-09 FIX] Bungkus proses check-in ke dalam DB::transaction dengan lockForUpdate untuk mencegah race condition
            $result = DB::transaction(function () use (
                $user, $today, $waktuSekarang, $shiftType, $scheduledInStr, $scheduledOutStr,
                $request, $distanceMeters, $isInRadius, $isLate, $isWorkingOnOffDay, $isFaceVerified, $reason, $evidencePath
            ) {
                // Periksa apakah karyawan sudah melakukan absen masuk hari ini dengan lockForUpdate
                $attendance = Kehadiran::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->lockForUpdate()
                    ->first();

                if ($attendance && $attendance->is_rest_after_overtime) {
                    return [
                        'status'  => 422,
                        'message' => 'Anda tidak dapat melakukan absen masuk hari ini karena telah memilih Menginap (Lembur) di Station dan wajib beristirahat.',
                    ];
                }

                if ($attendance && $attendance->check_in !== null) {
                    return [
                        'status'  => 400,
                        'message' => 'Anda sudah melakukan absen masuk hari ini!',
                    ];
                }

                $payloadIn = [
                    'shift_type'              => $shiftType,
                    'scheduled_in'            => $scheduledInStr,
                    'scheduled_out'           => $scheduledOutStr,
                    'check_in'                => $waktuSekarang,
                    'check_in_lat'            => (string) $request->latitude,
                    'check_in_long'           => (string) $request->longitude,
                    'check_in_distance'       => round($distanceMeters, 2),
                    'is_in_radius_check_in'   => $isInRadius,
                    'is_late'                 => $isLate,
                    'is_off_day_work'         => $isWorkingOnOffDay,
                    'is_rest_after_overtime'  => false,
                    'is_face_verified_in'     => $isFaceVerified,
                    'reason_in'               => !empty($reason) ? $reason : null,
                    'reason_out_of_radius_in' => !empty($reason) ? $reason : null,
                    'evidence_in'             => $evidencePath,
                    'status'                  => $isLate ? 'Terlambat' : 'Hadir',
                ];

                if ($attendance) {
                    $attendance->update($payloadIn);
                    $absensi = $attendance->fresh();
                } else {
                    $absensi = Kehadiran::create(array_merge([
                        'user_id' => $user->id,
                        'date'    => $today,
                    ], $payloadIn));
                }

                return [
                    'status'  => 200,
                    'absensi' => $absensi,
                ];
            });

            if ($result['status'] !== 200) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['status']);
            }

            $locName = $matchedStation ? $matchedStation->name : ($nearestStation ? 'Luar Radius (' . $nearestStation->name . ')' : 'Lokasi Terdaftar');
            $successMsg = $isWorkingOnOffDay
                ? 'Berhasil melakukan absen masuk di hari OFF (' . $locName . '). Kehadiran & keterangan kerja hari libur Anda telah tercatat.'
                : 'Berhasil melakukan absen masuk di ' . $locName . '. Selamat bekerja!';

            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'data'    => $result['absensi'],
            ], 200);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Error CheckIn: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memproses absensi masuk. Silakan coba kembali.',
            ], 500);
        }
    }

    // Memproses absensi pulang (Clock Out) karyawan
    public function checkOut(Request $request): JsonResponse|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();

            // Validasi data biometrik wajah terdaftar
            if (empty($user->face_descriptor) || !is_array($user->face_descriptor) || count($user->face_descriptor) !== 128) {
                $errorMsg = 'Data biometrik wajah belum terdaftar. Silakan rekam wajah di menu profil terlebih dahulu sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['face_descriptor' => [$errorMsg]],
                ], 422);
            }

            // Validasi verifikasi biometrik wajah
            if (!$request->boolean('is_face_verified', false)) {
                $errorMsg = 'Verifikasi biometrik wajah wajib berhasil sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['is_face_verified' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['is_face_verified' => [$errorMsg]],
                ], 422);
            }

            // Validasi uji keaktifan wajah (Liveness Anti-Spoofing) jika dikirimkan oleh klien
            if ($request->has('is_liveness_verified') && !$request->boolean('is_liveness_verified', false)) {
                $errorMsg = 'Verifikasi keaktifan wajah (Liveness Anti-Spoofing) wajib berhasil sebelum melakukan presensi.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['is_liveness_verified' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['is_liveness_verified' => [$errorMsg]],
                ], 422);
            }

            // [SEC-01 FIX] Validasi Time-Based Nonce Challenge Biometrik
            $nonce = $request->input('biometric_nonce');
            if (!empty($nonce)) {
                $cachedNonce = Cache::pull("biometric_nonce_{$user->id}_{$nonce}");
                if (!$cachedNonce || now()->timestamp > ($cachedNonce['expires_at'] ?? 0)) {
                    $errorMsg = 'Sesi verifikasi biometrik kamera telah kedaluwarsa. Silakan ulangi pemindaian wajah.';
                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
            } elseif (!app()->environment('testing') && $request->has('face_descriptor')) {
                $errorMsg = 'Token sesi biometrik (nonce) kamera tidak valid atau kedaluwarsa.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            // [SEC-01 FIX] Anti-Replay: Deteksi Duplikasi Frame Selfie (Hash Deduplication)
            if ($request->hasFile('selfie_frame')) {
                $selfieFile = $request->file('selfie_frame');
                if ($selfieFile->isValid()) {
                    $selfieHash = hash_file('sha256', $selfieFile->getRealPath());
                    $lastHashKey = "last_selfie_hash_{$user->id}";
                    $lastHash = Cache::get($lastHashKey);

                    if ($lastHash && hash_equals($lastHash, $selfieHash)) {
                        $errorMsg = 'Terdeteksi frame foto statis/duplikat (Replay Attack). Presensi wajib melalui pemindaian kamera langsung.';
                        if (!$request->expectsJson() && !$request->ajax()) {
                            return back()->withErrors(['face_descriptor' => $errorMsg]);
                        }
                        return response()->json(['success' => false, 'message' => $errorMsg], 422);
                    }
                    Cache::put($lastHashKey, $selfieHash, now()->addHours(8));
                }
            }

            // [SEC-02 FIX] Validasi Akurasi Sinyal GPS
            $accuracy = $request->input('accuracy');
            if (is_numeric($accuracy) && (float)$accuracy > 150) {
                $errorMsg = 'Akurasi GPS terlalu rendah (' . round((float)$accuracy) . ' meter). Pastikan GPS aktif dan berada di area terbuka.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['latitude' => $errorMsg]);
                }
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }

            // [SEC-01 FIX] Server-Side Biometric Verification:
            // Wajibkan verifikasi face_descriptor aktual dari kamera terhadap profil terdaftar (Euclidean Distance <= 0.58)
            $liveDescriptor = $request->input('face_descriptor');
            if (is_string($liveDescriptor)) {
                $liveDescriptor = json_decode($liveDescriptor, true);
            }

            if (!empty($liveDescriptor)) {
                if (!is_array($liveDescriptor) || count($liveDescriptor) !== 128) {
                    $errorMsg = 'Data biometrik wajah dari kamera tidak valid (wajib 128 dimensi float).';
                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                        'errors'  => ['face_descriptor' => [$errorMsg]],
                    ], 422);
                }

                // Validasi bahwa seluruh elemen adalah float/numerik
                foreach ($liveDescriptor as $val) {
                    if (!is_numeric($val)) {
                        $errorMsg = 'Nilai vektor biometrik wajah harus berupa float yang valid.';
                        if (!$request->expectsJson() && !$request->ajax()) {
                            return back()->withErrors(['face_descriptor' => $errorMsg]);
                        }

                        return response()->json([
                            'success' => false,
                            'message' => $errorMsg,
                            'errors'  => ['face_descriptor' => [$errorMsg]],
                        ], 422);
                    }
                }

                $distance = $this->calculateEuclideanDistance($user->face_descriptor, $liveDescriptor);
                if ($distance > 0.58) {
                    $errorMsg = 'Verifikasi biometrik wajah server-side gagal: Wajah tidak cocok dengan profil terdaftar Anda.';
                    Log::warning("Pencocokan wajah server-side gagal saat check-out untuk user ID {$user->id}. Distance: {$distance}");

                    if (!$request->expectsJson() && !$request->ajax()) {
                        return back()->withErrors(['face_descriptor' => $errorMsg]);
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg,
                        'errors'  => ['face_descriptor' => [$errorMsg]],
                    ], 422);
                }
            } elseif (!app()->environment('testing')) {
                // Di luar testing, wajib menyertakan live face descriptor untuk mencegah bypass via API/curl
                $errorMsg = 'Data biometrik wajah kamera wajib disertakan saat melakukan presensi pulang.';
                if (!$request->expectsJson() && !$request->ajax()) {
                    return back()->withErrors(['face_descriptor' => $errorMsg]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'errors'  => ['face_descriptor' => [$errorMsg]],
                ], 422);
            }

            // [SEC-10 FIX] Validasi range koordinat GPS latitude [-90,90] dan longitude [-180,180]
            $request->validate([
                'latitude'             => 'required|numeric|between:-90,90',
                'longitude'            => 'required|numeric|between:-180,180',
                'is_liveness_verified' => 'nullable|boolean',
                'face_descriptor'      => 'nullable',
                'reason'               => 'nullable|string|max:500',
                'reason_checkout'      => 'nullable|string|max:500',
                'cross_day_resolution' => 'nullable|in:forgot_checkout,overnight_overtime',
                'evidence'             => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'bukti_alasan'         => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);

            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            // 1. Tentukan record target absen pulang (hari ini atau sesi tanggal sebelumnya yang belum checkout)
            $todayAttendance = Kehadiran::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();

            $openPrevious = Kehadiran::where('user_id', $user->id)
                ->whereDate('date', '<', $today)
                ->whereNotNull('check_in')
                ->whereNull('check_out')
                ->orderBy('date', 'desc')
                ->first();

            $isCrossDay = false;
            $preAttendance = null;

            if ($todayAttendance && $todayAttendance->check_in !== null) {
                if ($todayAttendance->check_out !== null) {
                    return response()->json([
                        'message' => 'Anda sudah melakukan absen pulang hari ini!',
                    ], 400);
                }
                $preAttendance = $todayAttendance;
            } elseif ($openPrevious) {
                $preAttendance = $openPrevious;
                $isCrossDay = true;
            } else {
                return response()->json([
                    'message' => 'Gagal! Anda belum melakukan absen masuk hari ini.',
                ], 400);
            }

            $targetDateStr = $preAttendance->date->format('Y-m-d');
            $needsCrossDayResolution = false;
            $resolvedType = 'normal';

            if ($isCrossDay) {
                $yesterdayStr = $now->copy()->subDay()->format('Y-m-d');
                $isNightShiftFromYesterday = $targetDateStr === $yesterdayStr
                    && (
                        stripos((string) $preAttendance->shift_type, 'malam') !== false
                        || (!empty($preAttendance->scheduled_out) && !empty($preAttendance->scheduled_in) && $preAttendance->scheduled_out < $preAttendance->scheduled_in)
                    );

                // Shift malam kemarin yang pulang di pagi hari (< 11:00) tetap diproses normal tanpa popup menginap/lupa
                if ($isNightShiftFromYesterday && $now->hour < 11) {
                    $needsCrossDayResolution = false;
                    $resolvedType = 'normal';
                } else {
                    $needsCrossDayResolution = true;
                    $resolution = $request->input('cross_day_resolution');
                    if (!in_array($resolution, ['forgot_checkout', 'overnight_overtime'], true)) {
                        return response()->json([
                            'message' => 'Anda melakukan absen pulang untuk sesi tanggal ' . Carbon::parse($targetDateStr)->format('d/m/Y') . '. Silakan pilih keterangan apakah lupa absen pulang atau menginap (lembur) di station.',
                            'requires_cross_day_resolution' => true,
                            'target_date' => $targetDateStr,
                        ], 422);
                    }
                    $resolvedType = $resolution;
                }
            }

            // 2. Evaluasi geolokasi pulang terhadap seluruh stasiun dan Rumah Meter
            $geo = $this->evaluateGeofence((float) $request->latitude, (float) $request->longitude);
            $isInRadius = $geo['isInRadius'];
            $matchedStation = $geo['matchedStation'];
            $nearestStation = $geo['nearestStation'];
            $distanceMeters = $geo['distanceMeters'];

            // 3. Evaluasi apakah karyawan pulang lebih awal
            $isEarly = false;
            if (!$isCrossDay && !empty($preAttendance->scheduled_out) && $preAttendance->scheduled_out !== '--:--') {
                try {
                    $currentMinutes = $now->hour * 60 + $now->minute;
                    [$hOut, $mOut] = explode(':', $preAttendance->scheduled_out);
                    [$hIn, $mIn]   = explode(':', $preAttendance->scheduled_in ?? '00:00');

                    $schedOutMinutes = ((int) $hOut) * 60 + ((int) $mOut);
                    $schedInMinutes  = ((int) $hIn) * 60 + ((int) $mIn);

                    // Shift lintas hari (misal masuk 19:00, pulang 07:00)
                    if ($schedOutMinutes < $schedInMinutes) {
                        if ($currentMinutes >= $schedInMinutes || $currentMinutes < $schedOutMinutes) {
                            $isEarly = true;
                        }
                    } else {
                        if ($currentMinutes < $schedOutMinutes) {
                            $isEarly = true;
                        }
                    }
                } catch (\Exception $e) {
                    $isEarly = false;
                }
            } elseif ($isCrossDay && !$needsCrossDayResolution && !empty($preAttendance->scheduled_out) && $preAttendance->scheduled_out !== '--:--') {
                // Shift malam kemarin yang check-out pagi ini sebelum pukul 11:00
                try {
                    [$hOut, $mOut] = explode(':', $preAttendance->scheduled_out);
                    $schedOutToday = $now->copy()->startOfDay()->setTime((int) $hOut, (int) $mOut);
                    if ($now->lt($schedOutToday)) {
                        $isEarly = true;
                    }
                } catch (\Exception $e) {
                    $isEarly = false;
                }
            }

            // 4. Validasi alasan jika pulang awal atau berada di luar radius
            $reason = trim((string) ($request->reason ?? $request->reason_checkout));
            if ((!$isInRadius || $isEarly) && empty($reason)) {
                $kondisi = [];
                if ($isEarly) $kondisi[] = 'pulang sebelum jam selesai kerja';
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'stasiun terdekat';
                    $kondisi[] = 'berada di luar radius seluruh stasiun & Rumah Meter (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }

                return response()->json([
                    'message' => 'Harap isi alasan wajib karena Anda ' . implode(' dan ', $kondisi) . '!',
                ], 422);
            }

            $crossDayLabel = match ($resolvedType) {
                'forgot_checkout'    => '[Lupa Absen Pulang Kemarin]',
                'overnight_overtime' => '[Menginap / Lembur di Station]',
                default              => null,
            };
            $finalReasonOut = trim(implode(' ', array_filter([$crossDayLabel, $reason]))) ?: null;

            // 5. Proses unggah dan watermark dokumen/foto bukti alasan
            $evidenceFile = $request->file('evidence') ?? $request->file('bukti_alasan');
            $evidencePath = null;

            if ($evidenceFile) {
                $statusWatermark = match ($resolvedType) {
                    'forgot_checkout'    => 'LUPA ABSEN PULANG',
                    'overnight_overtime' => 'MENGINAP LEMBUR',
                    default              => ($isEarly ? 'PULANG AWAL' : 'SELESAI SHIFT'),
                };
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'Stasiun';
                    $statusWatermark .= ' | LUAR RADIUS (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }
                $evidencePath = $this->processAndWatermarkEvidence($evidenceFile, 'checkout', $user, $statusWatermark, $now);
            }

            $isFaceVerified = true;

            // [SEC-09 FIX] Bungkus proses check-out ke dalam DB::transaction dengan lockForUpdate
            $result = DB::transaction(function () use (
                $user, $today, $targetDateStr, $preAttendance, $resolvedType, $waktuSekarang, $request,
                $distanceMeters, $isInRadius, $isEarly, $isFaceVerified, $reason, $finalReasonOut, $evidencePath
            ) {
                $attendance = Kehadiran::where('id', $preAttendance->id)
                    ->lockForUpdate()
                    ->first();

                if (!$attendance || $attendance->check_in === null) {
                    return [
                        'status'  => 400,
                        'message' => 'Gagal! Data absen masuk tidak ditemukan.',
                    ];
                }

                if ($attendance->check_out !== null) {
                    return [
                        'status'  => 400,
                        'message' => 'Anda sudah melakukan absen pulang untuk sesi tanggal tersebut!',
                    ];
                }

                // Simpan catatan absensi pulang ke database
                $attendance->update([
                    'check_out'              => $waktuSekarang,
                    'checkout_date'          => $today,
                    'checkout_type'          => $resolvedType,
                    'check_out_lat'          => (string) $request->latitude,
                    'check_out_long'         => (string) $request->longitude,
                    'check_out_distance'     => round($distanceMeters, 2),
                    'is_in_radius_check_out' => $isInRadius,
                    'is_early_checkout'      => $isEarly,
                    'is_face_verified_out'   => $isFaceVerified,
                    'reason_out'             => $finalReasonOut,
                    'reason_checkout'        => $finalReasonOut,
                    'evidence_out'           => $evidencePath,
                ]);

                // Jika memilih Menginap (Lembur) di station, kunci hari ini sebagai Istirahat Pasca Lembur
                if ($resolvedType === 'overnight_overtime') {
                    $todayLock = Kehadiran::where('user_id', $user->id)
                        ->whereDate('date', $today)
                        ->lockForUpdate()
                        ->first();

                    if (!$todayLock || $todayLock->check_in === null) {
                        $restPayload = [
                            'shift_type'             => 'Istirahat Pasca Lembur',
                            'status'                 => 'Libur',
                            'is_rest_after_overtime' => true,
                            'reason_in'              => 'Istirahat wajib pasca menginap / lembur di station pada ' . Carbon::parse($targetDateStr)->format('d/m/Y') . (!empty($reason) ? ' (' . $reason . ')' : ''),
                        ];

                        if ($todayLock) {
                            $todayLock->update($restPayload);
                        } else {
                            Kehadiran::create(array_merge([
                                'user_id' => $user->id,
                                'date'    => $today,
                            ], $restPayload));
                        }
                    }
                }

                return [
                    'status'     => 200,
                    'attendance' => $attendance,
                ];
            });

            if ($result['status'] !== 200) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['status']);
            }

            $locName = $matchedStation ? $matchedStation->name : ($nearestStation ? 'Luar Radius (' . $nearestStation->name . ')' : 'Lokasi Terdaftar');
            $successMessage = match ($resolvedType) {
                'forgot_checkout'    => 'Absen pulang kemarin (' . Carbon::parse($targetDateStr)->format('d/m/Y') . ') tercatat sebagai Lupa Absen Pulang. Anda dapat melakukan absen masuk untuk hari ini.',
                'overnight_overtime' => 'Absen pulang kemarin (' . Carbon::parse($targetDateStr)->format('d/m/Y') . ') tercatat sebagai Menginap / Lembur di Station. Hari ini Anda wajib beristirahat dan tidak dapat absen masuk.',
                default              => 'Berhasil melakukan absen pulang di ' . $locName . '. Hati-hati di jalan!',
            };

            return response()->json([
                'success'               => true,
                'message'               => $successMessage,
                'cross_day_resolution'  => $resolvedType,
                'locked_today_for_rest' => $resolvedType === 'overnight_overtime',
                'data'                  => $result['attendance'],
            ], 200);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Error CheckOut: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem saat memproses absensi pulang. Silakan coba kembali.',
            ], 500);
        }
    }

    // Evaluasi geofence koordinat GPS pengguna terhadap seluruh stasiun resmi
    public function evaluateGeofence(float $userLat, float $userLng): array
    {
        // [M-05 FIX] Cache daftar stasiun selama 5 menit untuk optimasi performa dan mencegah N+1 DB hit
        $allStations = Cache::remember('all_stations_geofence', 300, function () {
            return Station::all();
        });
        $matchedStation = null;
        $nearestStation = null;
        $shortestDistance = PHP_FLOAT_MAX;
        $isInRadius = false;
        $distanceMeters = 0.0;

        foreach ($allStations as $st) {
            if (!empty($st->latitude) && !empty($st->longitude)) {
                $dist = $this->calculateDistance(
                    $userLat,
                    $userLng,
                    (float) $st->latitude,
                    (float) $st->longitude
                );

                if ($dist < $shortestDistance) {
                    $shortestDistance = $dist;
                    $nearestStation = $st;
                }

                $radiusLimit = (float) ($st->radius_meters ?? 100);
                if ($dist <= $radiusLimit) {
                    $matchedStation = $st;
                    $isInRadius = true;
                    $distanceMeters = $dist;
                    break;
                }
            }
        }

        if (!$isInRadius) {
            $distanceMeters = $shortestDistance !== PHP_FLOAT_MAX ? $shortestDistance : 0.0;
        }

        return [
            'isInRadius'     => $isInRadius,
            'matchedStation' => $matchedStation,
            'nearestStation' => $nearestStation,
            'distanceMeters' => $distanceMeters,
        ];
    }

    // Hitung jarak geolokasi dalam meter menggunakan formula Haversine
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Memproses berkas bukti alasan, memberikan watermark otomatis, dan kompresi gambar
    public function processAndWatermarkEvidence($file, string $type, User $user, string $statusText, ?Carbon $timestamp = null): ?string
    {
        try {
            if (!$file || !$file->isValid()) {
                return null;
            }

            $timestamp = $timestamp ?? Carbon::now('Asia/Jakarta');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'bukti_' . $type . '_' . $user->id . '_' . time() . '_' . uniqid() . '.' . ($extension === 'pdf' ? 'pdf' : 'jpg');
            $storageDir = 'bukti_alasan';

            if (!Storage::disk('public')->exists($storageDir)) {
                Storage::disk('public')->makeDirectory($storageDir);
            }

            // Jika file PDF, simpan langsung
            if ($extension === 'pdf') {
                return $file->storeAs($storageDir, $filename, 'public');
            }

            // Jika file Gambar, beri Watermark Otomatis via GD Library
            $imageContent = file_get_contents($file->getRealPath());
            $srcImage = @imagecreatefromstring($imageContent);

            if (!$srcImage) {
                return $file->storeAs($storageDir, $filename, 'public');
            }

            $origWidth = imagesx($srcImage);
            $origHeight = imagesy($srcImage);

            // Resize jika gambar terlalu besar (> 1280px) untuk efisiensi storage
            $maxWidth = 1280;
            if ($origWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) round(($origHeight * $maxWidth) / $origWidth);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                imagedestroy($srcImage);
                $srcImage = $resized;
                $origWidth = $newWidth;
                $origHeight = $newHeight;
            }

            // Siapkan teks watermark
            $line1 = "TIRTA UMBULAN ERP | PRESENSI BUKTI KHUSUS";
            $line2 = "Karyawan : " . $user->name . " (NIP: " . ($user->nip ?? '-') . ")";
            $line3 = "Waktu    : " . $timestamp->format('d F Y - H:i:s') . " WIB";
            $line4 = "Status   : " . $statusText;

            $bannerHeight = 80;
            $bannerY = $origHeight - $bannerHeight;

            // Gambar banner hitam transparan di bagian bawah foto
            imagealphablending($srcImage, true);
            $bgBanner = imagecolorallocatealpha($srcImage, 15, 23, 42, 35); // Slate 900 semi-transparent
            imagefilledrectangle($srcImage, 0, $bannerY, $origWidth, $origHeight, $bgBanner);

            // Warna teks
            $textColorWhite = imagecolorallocate($srcImage, 255, 255, 255);
            $textColorGold  = imagecolorallocate($srcImage, 251, 191, 36); // Amber 400

            // Tulis teks watermark menggunakan built-in font PHP GD
            imagestring($srcImage, 3, 15, $bannerY + 8, $line1, $textColorGold);
            imagestring($srcImage, 2, 15, $bannerY + 25, $line2, $textColorWhite);
            imagestring($srcImage, 2, 15, $bannerY + 42, $line3, $textColorWhite);
            imagestring($srcImage, 2, 15, $bannerY + 59, $line4, $textColorGold);

            // Simpan gambar yang telah dibubuhi watermark dengan kualitas kompresi 75%
            ob_start();
            imagejpeg($srcImage, null, 75);
            $compressedImageData = ob_get_clean();
            imagedestroy($srcImage);

            $savePath = $storageDir . '/' . $filename;
            Storage::disk('public')->put($savePath, $compressedImageData);

            return $savePath;

        } catch (\Exception $e) {
            Log::error("Gagal memproses watermark bukti kehadiran: " . $e->getMessage());
            return $file->store('bukti_alasan', 'public');
        }
    }

    // [C-01 FIX] Menghitung Euclidean Distance antara dua vektor descriptor 128-dimensi
    public function calculateEuclideanDistance(array $desc1, array $desc2): float
    {
        if (count($desc1) !== 128 || count($desc2) !== 128) {
            return 999.0;
        }

        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = (float)$desc1[$i] - (float)$desc2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }
}
