<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function updateSchedule(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'schedule_type' => 'required|in:normal,roster',
            'normal_work_days' => 'nullable|array',
            'normal_check_in' => 'nullable|date_format:H:i',
            'normal_check_out' => 'nullable|date_format:H:i',
            'roster_start_date' => 'nullable|date',
        ]);

        $user->update([
            'schedule_type' => $request->schedule_type,
            'normal_work_days' => $request->schedule_type === 'normal' ? $request->normal_work_days : null,
            'normal_check_in' => $request->schedule_type === 'normal' ? ($request->normal_check_in ?? '08:00') : '08:00',
            'normal_check_out' => $request->schedule_type === 'normal' ? ($request->normal_check_out ?? '17:00') : '17:00',
            'roster_start_date' => $request->schedule_type === 'roster' ? $request->roster_start_date : null,
        ]);

        return response()->json([
            'message' => 'Pengaturan jadwal kerja berhasil diperbarui.',
            'user' => $user
        ], 200);
    }

    public function registerFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required|array', 
        ]);

        $user = $request->user();
        $user->update([
            'face_descriptor' => $request->face_descriptor,
        ]);

        return response()->json([
            'message' => 'Data verifikasi wajah berhasil disimpan!'
        ], 200);
    }

    public function setInitialShift(Request $request)
    {
        $request->validate([
            'current_shift' => 'required|in:pagi,malam,libur',
        ]);

        $user = $request->user();
        $today = Carbon::today();

        // Cari hari Selasa pada minggu berjalan
        $currentTuesday = $today->copy()->startOfWeek(Carbon::TUESDAY);
        if ($today->lt($currentTuesday)) {
            $currentTuesday->subWeek();
        }

        // Tentukan tanggal acuan (roster_start_date) berdasarkan pilihan shift
        // Siklus: Pagi (0 minggu) -> Malam (1 minggu mundur) -> Libur (2 minggu mundur)
        if ($request->current_shift === 'pagi') {
            $rosterStartDate = $currentTuesday;
        } elseif ($request->current_shift === 'malam') {
            $rosterStartDate = $currentTuesday->copy()->subWeek();
        } else {
            $rosterStartDate = $currentTuesday->copy()->subWeeks(2);
        }

        $user->update([
            'roster_start_date' => $rosterStartDate->format('Y-m-d'),
        ]);

        return redirect()->back()->with('success', 'Shift berhasil dikonfirmasi! Rotasi shift Anda akan otomatis berganti setiap hari Selasa.');
    }
}