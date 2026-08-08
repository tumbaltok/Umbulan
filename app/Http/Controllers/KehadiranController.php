<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            return redirect()->back()->with('error', 'Anda sudah melakukan absen masuk hari ini!');
        }

        $schedule = $this->scheduleService->getTodaySchedule($user, $today);
        if ($schedule['is_day_off']) {
            return redirect()->back()->with('error', 'Hari ini adalah jadwal libur Anda.');
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
            return redirect()->back()->withErrors([
                'reason_out_of_radius' => 'Anda berada di luar radius lokasi stasiun. Harap isi alasan berada di luar radius!'
            ])->withInput();
        }

        $facePath = null;
        if ($request->filled('face_image')) {
            $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkin');
        }

        Kehadiran::updateOrCreate(
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

        return redirect()->back()->with('success', 'Berhasil melakukan absen masuk. Selamat bekerja!');
    }

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

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum melakukan absen masuk hari ini!');
        }

        if ($attendance->check_out !== null) {
            return redirect()->back()->with('error', 'Anda sudah melakukan absen pulang hari ini!');
        }

        $scheduledOut = Carbon::parse($today . ' ' . $attendance->scheduled_out);
        $isEarlyCheckout = $now->lt($scheduledOut);

        if ($isEarlyCheckout && !$request->filled('reason_checkout')) {
            return redirect()->back()->withErrors([
                'reason_checkout' => 'Alasan pulang cepat wajib diisi.'
            ])->withInput();
        }

        $facePath = null;
        if ($request->filled('face_image')) {
            $facePath = $this->saveBase64Image($request->face_image, 'attendance/checkout');
        }

        $attendance->update([
            'check_out' => $now->format('H:i:s'),
            'check_out_lat' => $request->latitude,
            'check_out_long' => $request->longitude,
            'is_early_checkout' => $isEarlyCheckout,
            'reason_checkout' => $request->reason_checkout,
            'face_photo_out' => $facePath,
        ]);

        return redirect()->back()->with('success', 'Absen pulang berhasil dikirim!');
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2)
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

    private function saveBase64Image(string $base64String, string $folder)
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