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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KehadiranController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
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

            // Validasi keberhasilan pencocokan biometrik wajah
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

            $request->validate([
                'latitude'             => 'required|numeric',
                'longitude'            => 'required|numeric',
                'is_face_verified'     => 'required|boolean',
                'reason'               => 'nullable|string',
                'reason_out_of_radius' => 'nullable|string',
                'evidence'             => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'bukti_alasan'         => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);

            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            // 1. Periksa apakah karyawan sudah melakukan absen masuk hari ini
            $attendance = Kehadiran::where('user_id', $user->id)
                ->where(function ($q) use ($today) {
                    $q->whereDate('date', $today)
                        ->orWhereDate('created_at', $today);
                })
                ->first();

            if ($attendance && $attendance->check_in !== null) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absen masuk hari ini!',
                ], 400);
            }

            // 2. Evaluasi jadwal kerja karyawan
            $schedule = $this->scheduleService->getTodaySchedule($user, $today) ?? [];
            if (isset($schedule['is_day_off']) && $schedule['is_day_off']) {
                return response()->json([
                    'message' => 'Hari ini adalah jadwal libur (OFF) Anda.',
                ], 400);
            }

            // 3. Evaluasi geolokasi terhadap seluruh stasiun dan Rumah Meter
            $geo = $this->evaluateGeofence((float) $request->latitude, (float) $request->longitude);
            $isInRadius = $geo['isInRadius'];
            $matchedStation = $geo['matchedStation'];
            $nearestStation = $geo['nearestStation'];
            $distanceMeters = $geo['distanceMeters'];

            // 4. Evaluasi status keterlambatan
            $isLate = false;
            $scheduledInStr = $schedule['scheduled_in'] ?? null;

            if (!empty($scheduledInStr) && $scheduledInStr !== '--:--') {
                try {
                    $scheduledIn = Carbon::parse($today . ' ' . $scheduledInStr, 'Asia/Jakarta');
                    if ($now->gt($scheduledIn)) {
                        $isLate = true;
                    }
                } catch (\Exception $e) {
                    $isLate = false;
                }
            }

            // 5. Validasi alasan jika terlambat atau berada di luar radius
            $reason = trim((string) ($request->reason ?? $request->reason_out_of_radius));
            if ((!$isInRadius || $isLate) && empty($reason)) {
                $kondisi = [];
                if ($isLate) $kondisi[] = 'terlambat';
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'stasiun terdekat';
                    $kondisi[] = 'berada di luar radius seluruh stasiun & Rumah Meter (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }

                return response()->json([
                    'message' => 'Harap isi alasan wajib karena Anda terdeteksi ' . implode(' dan ', $kondisi) . '!',
                ], 422);
            }

            // 6. Proses unggah dan watermark dokumen/foto bukti alasan
            $evidenceFile = $request->file('evidence') ?? $request->file('bukti_alasan');
            $evidencePath = null;

            if ($evidenceFile) {
                $statusWatermark = $isLate ? 'TERLAMBAT' : 'TEPAT WAKTU';
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'Stasiun';
                    $statusWatermark .= ' | LUAR RADIUS (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }
                $evidencePath = $this->processAndWatermarkEvidence($evidenceFile, 'checkin', $user, $statusWatermark, $now);
            }

            $shiftType = $schedule['shift_type'] ?? ($schedule['shift_name'] ?? 'Normal');
            $isFaceVerified = $request->boolean('is_face_verified', false);

            // 7. Simpan atau perbarui catatan absensi masuk
            $absensi = Kehadiran::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date'    => $today,
                ],
                [
                    'shift_type'              => $shiftType,
                    'scheduled_in'            => $scheduledInStr,
                    'scheduled_out'           => $schedule['scheduled_out'] ?? null,
                    'check_in'                => $waktuSekarang,
                    'check_in_lat'            => (string) $request->latitude,
                    'check_in_long'           => (string) $request->longitude,
                    'check_in_distance'       => round($distanceMeters, 2),
                    'is_in_radius_check_in'   => $isInRadius,
                    'is_late'                 => $isLate,
                    'is_face_verified_in'     => $isFaceVerified,
                    'reason_in'               => !empty($reason) ? $reason : null,
                    'reason_out_of_radius_in' => !empty($reason) ? $reason : null,
                    'evidence_in'             => $evidencePath,
                    'status'                  => $isLate ? 'Terlambat' : 'Hadir',
                ]
            );

            $locName = $matchedStation ? $matchedStation->name : ($nearestStation ? 'Luar Radius (' . $nearestStation->name . ')' : 'Lokasi Terdaftar');
            return response()->json([
                'success' => true,
                'message' => 'Berhasil melakukan absen masuk di ' . $locName . '. Selamat bekerja!',
                'data'    => $absensi,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error CheckIn: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage(),
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

            $request->validate([
                'latitude'         => 'required|numeric',
                'longitude'        => 'required|numeric',
                'is_face_verified' => 'required|boolean',
                'reason'           => 'nullable|string',
                'reason_checkout'  => 'nullable|string',
                'evidence'         => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
                'bukti_alasan'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            ]);

            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            // 1. Periksa keberadaan catatan absen masuk hari ini
            $attendance = Kehadiran::where('user_id', $user->id)
                ->where(function ($q) use ($today) {
                    $q->whereDate('date', $today)
                        ->orWhereDate('created_at', $today);
                })
                ->first();

            if (!$attendance || $attendance->check_in === null) {
                return response()->json([
                    'message' => 'Gagal! Anda belum melakukan absen masuk hari ini.',
                ], 400);
            }

            if ($attendance->check_out !== null) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absen pulang hari ini!',
                ], 400);
            }

            // 2. Evaluasi geolokasi pulang terhadap seluruh stasiun dan Rumah Meter
            $geo = $this->evaluateGeofence((float) $request->latitude, (float) $request->longitude);
            $isInRadius = $geo['isInRadius'];
            $matchedStation = $geo['matchedStation'];
            $nearestStation = $geo['nearestStation'];
            $distanceMeters = $geo['distanceMeters'];

            // 3. Evaluasi apakah karyawan pulang lebih awal
            $isEarly = false;
            if (!empty($attendance->scheduled_out) && $attendance->scheduled_out !== '--:--') {
                try {
                    $currentMinutes = $now->hour * 60 + $now->minute;
                    [$hOut, $mOut] = explode(':', $attendance->scheduled_out);
                    [$hIn, $mIn]   = explode(':', $attendance->scheduled_in ?? '00:00');

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

            // 5. Proses unggah dan watermark dokumen/foto bukti alasan
            $evidenceFile = $request->file('evidence') ?? $request->file('bukti_alasan');
            $evidencePath = null;

            if ($evidenceFile) {
                $statusWatermark = $isEarly ? 'PULANG AWAL' : 'SELESAI SHIFT';
                if (!$isInRadius) {
                    $stName = $nearestStation ? $nearestStation->name : 'Stasiun';
                    $statusWatermark .= ' | LUAR RADIUS (' . round($distanceMeters) . 'm dari ' . $stName . ')';
                }
                $evidencePath = $this->processAndWatermarkEvidence($evidenceFile, 'checkout', $user, $statusWatermark, $now);
            }

            $isFaceVerified = $request->boolean('is_face_verified', false);

            // 6. Simpan catatan absensi pulang ke database
            $attendance->update([
                'check_out'              => $waktuSekarang,
                'check_out_lat'          => (string) $request->latitude,
                'check_out_long'         => (string) $request->longitude,
                'check_out_distance'     => round($distanceMeters, 2),
                'is_in_radius_check_out' => $isInRadius,
                'is_early_checkout'      => $isEarly,
                'is_face_verified_out'   => $isFaceVerified,
                'reason_out'             => !empty($reason) ? $reason : null,
                'reason_checkout'        => !empty($reason) ? $reason : null,
                'evidence_out'           => $evidencePath,
            ]);

            $locName = $matchedStation ? $matchedStation->name : ($nearestStation ? 'Luar Radius (' . $nearestStation->name . ')' : 'Lokasi Terdaftar');
            return response()->json([
                'success' => true,
                'message' => 'Berhasil melakukan absen pulang di ' . $locName . '. Hati-hati di jalan!',
                'data'    => $attendance,
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error CheckOut: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());

            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage(),
            ], 500);
        }
    }

    // Evaluasi geofence koordinat GPS pengguna terhadap seluruh stasiun resmi
    public function evaluateGeofence(float $userLat, float $userLng): array
    {
        $allStations = Station::all();
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
}
