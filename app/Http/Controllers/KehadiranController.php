<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Kehadiran;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class KehadiranController extends Controller
{
    protected ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'face_image' => 'nullable|string', 
            'reason_out_of_radius' => 'nullable|string',
        ]);

        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $attendance = Kehadiran::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance && $attendance->check_in) {
            return response()->json(['message' => 'Anda sudah melakukan absen masuk hari ini!'], 400);
        }

        $schedule = $this->scheduleService->getTodaySchedule($user, $today);
        if ($schedule['is_day_off']) {
            return response()->json(['message' => 'Hari ini adalah jadwal libur Anda.'], 400);
        }

        $station = $user->station;
        $isInRadius = true;
        if ($station && $station->latitude && $station->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude, $request->longitude,
                $station->latitude, $station->longitude
            );
            $isInRadius = $distance <= $station->radius_meters;
        }

        if (!$isInRadius && empty($request->reason_out_of_radius)) {
            return response()->json([
                'message' => 'Anda berada di luar radius lokasi stasiun. Harap isi alasan berada di luar radius!'
            ], 422);
        }

        $facePath = null;
        if ($request->face_image) {
            $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkin');
        }

        $attendance = Kehadiran::updateOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            [
                'shift_type' => $schedule['shift_type'],
                'scheduled_in' => $schedule['scheduled_in'],
                'scheduled_out' => $schedule['scheduled_out'],
                'check_in' => $now->format('H:i:s'),
                'check_in_lat' => $request->latitude,
                'check_in_long' => $request->longitude,
                'is_in_radius_check_in' => $isInRadius,
                'reason_out_of_radius_in' => !$isInRadius ? $request->reason_out_of_radius : null,
                'face_photo_in' => $facePath,
            ]
        );

        return response()->json([
            'message' => 'Berhasil melakukan absen masuk. Selamat bekerja!',
            'data' => $attendance
        ], 200);
    }

    // 2. Absen Pulang (Check-Out)
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'face_image' => 'nullable|string',
            'reason_checkout' => 'nullable|string',
        ]);

        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        $attendance = Kehadiran::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in) {
            return response()->json(['message' => 'Anda belum melakukan absen masuk hari ini!'], 400);
        }

        if ($attendance->check_out) {
            return response()->json(['message' => 'Anda sudah melakukan absen pulang hari ini!'], 400);
        }

        $station = $user->station;
        $isInRadius = true;
        if ($station && $station->latitude && $station->longitude) {
            $distance = $this->calculateDistance(
                $request->latitude, $request->longitude,
                $station->latitude, $station->longitude
            );
            $isInRadius = $distance <= $station->radius_meters;
        }

        $scheduledOut = Carbon::parse($today . ' ' . $attendance->scheduled_out);
        $isEarlyCheckout = $now->lt($scheduledOut);

        if ((!$isInRadius || $isEarlyCheckout) && empty($request->reason_checkout)) {
            $msg = !$isInRadius && $isEarlyCheckout 
                ? 'Anda berada di luar radius dan pulang lebih cepat dari jadwal. Berikan alasan!' 
                : (!$isInRadius ? 'Anda berada di luar radius lokasi stasiun. Berikan alasan!' : 'Anda pulang lebih cepat dari jadwal. Berikan alasan!');
            
            return response()->json(['message' => $msg], 422);
        }

        $facePath = null;
        if ($request->face_image) {
            $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkout');
        }

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'check_out_lat' => $request->latitude,
            'check_out_long' => $request->longitude,
            'is_in_radius_check_out' => $isInRadius,
            'is_early_checkout' => $isEarlyCheckout,
            'reason_checkout' => $request->reason_checkout,
            'face_photo_out' => $facePath,
        ]);

        return response()->json([
            'message' => 'Berhasil melakukan absen pulang. Hati-hati di jalan!',
            'data' => $attendance
        ], 200);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
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

    private function saveBase64Image($base64String, $folder)
    {
        $imageParts = explode(";base64,", $base64String);
        $imageTypeAux = explode("image/", $imageParts[0]);
        $imageType = $imageTypeAux[1] ?? 'png';
        $imageBase64 = base64_decode($imageParts[1] ?? $base64String);

        $fileName = $folder . '/' . uniqid() . '.' . $imageType;
        Storage::disk('public')->put($fileName, $imageBase64);

        return $fileName;
    }
}