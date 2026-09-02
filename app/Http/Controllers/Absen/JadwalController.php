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

    // Memperbarui pengaturan jadwal kerja karyawan (tipe normal atau roster)
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'schedule_type' => ['required', 'in:normal,roster'],
            'normal_work_days' => ['nullable', 'array'],
            'normal_check_in' => ['nullable', 'date_format:H:i'],
            'normal_check_out' => ['nullable', 'date_format:H:i'],
            'current_shift_choice' => ['nullable', 'in:pagi,malam,libur'],
            'roster_start_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $user = $request->user();
        $updateData = [
            'schedule_type' => $request->schedule_type,
        ];

        // Simpan data jadwal kerja tipe normal
        if ($request->schedule_type === 'normal') {
            $updateData['normal_work_days'] = $request->input('normal_work_days', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);

            if ($request->filled('normal_check_in')) {
                $updateData['normal_check_in'] = $request->normal_check_in;
            }
            if ($request->filled('normal_check_out')) {
                $updateData['normal_check_out'] = $request->normal_check_out;
            }
        } elseif ($request->schedule_type === 'roster') {
            // Simpan data jadwal kerja tipe roster rotasi 3 mingguan
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

        $user->update([
            'face_descriptor' => $descriptor,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perekaman biometrik wajah karyawan berhasil disimpan!',
        ]);
    }
}
