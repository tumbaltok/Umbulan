<?php

namespace App\Http\Controllers\Absen;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // Mengatur shift awal roster karyawan
    public function setInitialShift(Request $request)
    {
        $request->validate([
            'current_shift_choice' => ['required', 'in:pagi,malam,libur'],
        ]);

        $user = $request->user();
        $now = Carbon::now('Asia/Jakarta');

        // Tentukan acuan hari Selasa pergantian roster shift
        $currentTuesday = $now
            ->copy()
            ->startOfWeek(Carbon::TUESDAY)
            ->setTime(7, 0, 0);

        if (
            $now->dayOfWeekIso === Carbon::TUESDAY &&
            $now->lt($currentTuesday)
        ) {
            $currentTuesday->subWeek();
        }

        if ($currentTuesday->gt($now)) {
            $currentTuesday->subWeek();
        }

        $selectedShift = $request->input('current_shift_choice');

        // Hitung tanggal acuan mundur berdasarkan pilihan shift saat ini
        switch ($selectedShift) {
            case 'pagi':
                $rosterStartDate = $currentTuesday->copy();
                break;
            case 'malam':
                $rosterStartDate = $currentTuesday->copy()->subWeek();
                break;
            case 'libur':
                $rosterStartDate = $currentTuesday->copy()->subWeeks(2);
                break;
            default:
                abort(422, 'Shift tidak valid.');
        }

        $user->update([
            'schedule_type' => 'roster',
            'roster_start_date' => $rosterStartDate->format('Y-m-d'),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Shift berhasil dikonfirmasi. Rotasi otomatis setiap Selasa pukul 07:00 WIB.');
    }

    // Memperbarui pengaturan jadwal kerja karyawan (tipe Reguler 5 Hari, Reguler 6 Hari, atau Roster)
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'schedule_type' => ['required', 'in:reguler_5_hari,reguler_6_hari,roster,normal'],
            'current_shift_choice' => ['nullable', 'in:pagi,malam,libur'],
            'roster_start_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $scheduleType = $request->schedule_type;
        if ($scheduleType === 'normal') {
            $scheduleType = 'reguler_5_hari';
        }

        $user = $request->user();
        $updateData = [
            'schedule_type' => $scheduleType,
        ];

        // Tipe 1: Reguler 5 Hari (Senin - Jumat, 07:00 - 16:00, Sabtu-Minggu OFF)
        if ($scheduleType === 'reguler_5_hari') {
            $updateData['normal_work_days'] = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            $updateData['normal_check_in'] = '07:00:00';
            $updateData['normal_check_out'] = '16:00:00';
            $updateData['roster_start_date'] = null;
        } elseif ($scheduleType === 'reguler_6_hari') {
            // Tipe 2: Reguler 6 Hari (Senin - Sabtu; Sen-Jum 07:00 - 16:00, Sab 07:00 - 12:00, Minggu OFF)
            $updateData['normal_work_days'] = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $updateData['normal_check_in'] = '07:00:00';
            $updateData['normal_check_out'] = '16:00:00';
            $updateData['roster_start_date'] = null;
        } elseif ($scheduleType === 'roster') {
            // Tipe 3: Roster / Shift (Rotasi 3 mingguan setiap Selasa pukul 07:00 WIB)
            $updateData['normal_work_days'] = null;
            $updateData['normal_check_in'] = null;
            $updateData['normal_check_out'] = null;

            $selectedShift = $request->input('current_shift_choice');

            if ($selectedShift) {
                $now = Carbon::now('Asia/Jakarta');
                $currentTuesday = $now
                    ->copy()
                    ->startOfWeek(Carbon::TUESDAY)
                    ->setTime(7, 0, 0);

                if (
                    $now->dayOfWeekIso === Carbon::TUESDAY &&
                    $now->lt($currentTuesday)
                ) {
                    $currentTuesday->subWeek();
                }

                if ($currentTuesday->gt($now)) {
                    $currentTuesday->subWeek();
                }

                switch ($selectedShift) {
                    case 'pagi':
                        $rosterStartDate = $currentTuesday->copy();
                        break;
                    case 'malam':
                        $rosterStartDate = $currentTuesday->copy()->subWeek();
                        break;
                    case 'libur':
                        $rosterStartDate = $currentTuesday->copy()->subWeeks(2);
                        break;
                    default:
                        abort(422, 'Shift roster tidak valid.');
                }

                $updateData['roster_start_date'] = $rosterStartDate->format('Y-m-d');
            } elseif ($request->filled('roster_start_date')) {
                $updateData['roster_start_date'] = Carbon::parse($request->roster_start_date, 'Asia/Jakarta')->format('Y-m-d');
            }
        }

        $user->update($updateData);

        return redirect()
            ->back()
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    // Merekam biometrik wajah karyawan (penguncian permanen 1x demi keamanan)
    public function registerFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required',
        ]);

        $user = $request->user();

        // Proteksi biometrik: tolak jika data biometrik wajah sudah terkunci
        if (!empty($user->face_descriptor)) {
            return response()->json([
                'success' => false,
                'message' => 'Data biometrik wajah Anda sudah terkunci dan tidak dapat diubah kembali demi keamanan.',
            ], 403);
        }

        $descriptor = $request->face_descriptor;
        if (is_string($descriptor)) {
            $decoded = json_decode($descriptor, true);
            if (is_array($decoded)) {
                $descriptor = $decoded;
            }
        }

        // [H-06 FIX] Validasi ketat format vektor biometrik wajah (wajib 128 elemen float numerik)
        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return response()->json([
                'success' => false,
                'message' => 'Format vektor biometrik tidak valid. Wajib berupa 128 nilai koordinat numerik.',
            ], 422);
        }

        foreach ($descriptor as $val) {
            if (!is_numeric($val) || $val < -2.0 || $val > 2.0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nilai elemen vektor biometrik berada di luar rentang valid.',
                ], 422);
            }
        }

        $user->update([
            'face_descriptor' => array_map('floatval', $descriptor),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perekaman biometrik wajah karyawan berhasil disimpan!',
        ]);
    }
}
