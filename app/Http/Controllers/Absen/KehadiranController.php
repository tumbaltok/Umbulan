<?php

namespace App\Http\Controllers\Absen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absen\Kehadiran;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class KehadiranController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Absen Masuk (Clock In) via AJAX
     */
    public function checkIn(Request $request)
    {
        try {
            $request->validate([
                'latitude'             => 'required',
                'longitude'            => 'required',
                'face_image'           => 'nullable|string', 
                'reason_out_of_radius' => 'nullable|string',
            ]);

            $user = $request->user();
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            // Cek apakah sudah absen masuk hari ini
            $attendance = Kehadiran::where('user_id', $user->id)
                ->where(function($q) use ($today) {
                    $q->whereDate('date', $today)
                      ->orWhereDate('created_at', $today);
                })
                ->first();

            if ($attendance && $attendance->check_in !== null) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absen masuk hari ini!'
                ], 400);
            }

            // Cek Jadwal Kerja
            $schedule = $this->scheduleService->getTodaySchedule($user, $today) ?? [];
            if (isset($schedule['is_day_off']) && $schedule['is_day_off']) {
                return response()->json([
                    'message' => 'Hari ini adalah jadwal libur (OFF) Anda.'
                ], 400);
            }

            // Hitung Radius GPS Stasiun Kerja
            $station = $user->station ?? null;
            $isInRadius = true;
            
            if ($station && !empty($station->latitude) && !empty($station->longitude)) {
                $distance = $this->calculateDistance(
                    (float)$request->latitude, (float)$request->longitude,
                    (float)$station->latitude, (float)$station->longitude
                );
                $radiusMeters = (float)($station->radius_meters ?? 100);
                $isInRadius = $distance <= $radiusMeters;
            } else {
                $isInRadius = false;
            }

            // Tentukan Keterangan Terlambat / Tepat Waktu
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

            // Jika Di Luar Radius atau Terlambat, Wajib Alasan
            $reason = $request->reason_out_of_radius ?? $request->reason;
            if ((!$isInRadius || $isLate) && empty(trim((string)$reason))) {
                return response()->json([
                    'message' => 'Harap isi alasan berada di luar area atau alasan keterlambatan Anda!'
                ], 422);
            }

            // Simpan Foto Jika Ada
            $facePath = null;
            if ($request->filled('face_image')) {
                $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkin');
            }

            $shiftType = $schedule['shift_type'] ?? ($schedule['shift_name'] ?? 'Normal');

            // Simpan Data Ke DB (Sesuaikan murni dengan struktur kolom migrasi 'kehadirans')
            $absensi = Kehadiran::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date'    => $today
                ],
                [
                    'shift_type'              => $shiftType,
                    'scheduled_in'            => $scheduledInStr,
                    'scheduled_out'           => $schedule['scheduled_out'] ?? null,
                    'check_in'                => $waktuSekarang,
                    'check_in_lat'            => (string)$request->latitude,
                    'check_in_long'           => (string)$request->longitude,
                    'is_in_radius_check_in'   => $isInRadius,
                    'reason_out_of_radius_in' => $reason,
                    'face_photo_in'           => $facePath,
                ]
            );

            return response()->json([
                'message' => 'Berhasil melakukan absen masuk. Selamat bekerja!',
                'data'    => $absensi
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error CheckIn: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Absen Pulang (Clock Out) via AJAX
     */
    public function checkOut(Request $request)
    {
        try {
            $request->validate([
                'latitude'        => 'required',
                'longitude'       => 'required',
                'face_image'      => 'nullable|string',
                'reason_checkout' => 'nullable|string',
            ]);

            $user = $request->user();
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $waktuSekarang = $now->format('H:i:s');

            $attendance = Kehadiran::where('user_id', $user->id)
                ->where(function($q) use ($today) {
                    $q->whereDate('date', $today)
                      ->orWhereDate('created_at', $today);
                })
                ->first();

            if (!$attendance || $attendance->check_in === null) {
                return response()->json([
                    'message' => 'Gagal! Anda belum melakukan absen masuk hari ini.'
                ], 400);
            }

            if ($attendance->check_out !== null) {
                return response()->json([
                    'message' => 'Anda sudah melakukan absen pulang hari ini!'
                ], 400);
            }

            $reason = $request->reason_checkout ?? $request->reason;

            // Simpan Foto Pulang
            $facePath = null;
            if ($request->filled('face_image')) {
                $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkout');
            }

            // Cek Pulang Awal
            $isEarly = false;
            if (!empty($attendance->scheduled_out)) {
                try {
                    $scheduledOut = Carbon::parse($today . ' ' . $attendance->scheduled_out, 'Asia/Jakarta');
                    if ($now->lt($scheduledOut)) {
                        $isEarly = true;
                    }
                } catch (\Exception $e) {
                    $isEarly = false;
                }
            }

            $attendance->update([
                'check_out'               => $waktuSekarang,
                'check_out_lat'           => (string)$request->latitude,
                'check_out_long'          => (string)$request->longitude,
                'is_in_radius_check_out'  => true,
                'is_early_checkout'       => $isEarly,
                'reason_checkout'         => $reason,
                'face_photo_out'          => $facePath,
            ]);

            return response()->json([
                'message' => 'Berhasil melakukan absen pulang. Hati-hati di jalan!',
                'data'    => $attendance
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error CheckOut: ' . $th->getMessage() . ' File: ' . $th->getFile() . ' Line: ' . $th->getLine());
            return response()->json([
                'message' => 'Terjadi kesalahan sistem: ' . $th->getMessage()
            ], 500);
        }
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function saveBase64Image(string $base64String, string $folder)
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
                $data = substr($base64String, strpos($base64String, ',') + 1);
                $type = strtolower($type[1]);

                if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                    $type = 'jpg';
                }

                $data = base64_decode($data);
                if ($data === false) {
                    return null;
                }
            } else {
                return null;
            }

            $fileName = $folder . '/' . uniqid() . '.' . $type;
            Storage::disk('public')->put($fileName, $data);

            return $fileName;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rekapHarian(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $karyawan = \App\Models\User\User::with(['station', 'role'])->orderBy('name', 'asc')->get();

        $kehadiran = Kehadiran::whereDate('date', $tanggal)
            ->orWhereDate('created_at', $tanggal)
            ->get()
            ->keyBy('user_id');

        $sudahAbsen = [];
        $belumAbsen = [];

        foreach ($karyawan as $user) {
            if (isset($kehadiran[$user->id]) && $kehadiran[$user->id]->check_in) {
                $sudahAbsen[] = [
                    'user'  => $user,
                    'absen' => $kehadiran[$user->id]
                ];
            } else {
                $belumAbsen[] = $user;
            }
        }

        return view('admin.record.absensi', compact('tanggal', 'sudahAbsen', 'belumAbsen', 'karyawan'));
    }
}