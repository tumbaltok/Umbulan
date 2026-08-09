<?php

namespace App\Http\Controllers\Absen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    /**
     * Mengatur Shift Awal Roster Karyawan
     */
    public function setInitialShift(Request $request)
    {
        $request->validate([
            'current_shift' => 'required|in:pagi,malam,libur',
        ]);

        $user = $request->user();
        
        // PERBAIKAN: Gunakan timezone Asia/Jakarta
        $today = Carbon::today('Asia/Jakarta');

        $currentTuesday = $today->copy()->startOfWeek(Carbon::TUESDAY);
        if ($today->lt($currentTuesday)) {
            $currentTuesday->subWeek();
        }

        if ($request->current_shift === 'pagi') {
            $rosterStartDate = $currentTuesday;
        } elseif ($request->current_shift === 'malam') {
            $rosterStartDate = $currentTuesday->copy()->subWeek();
        } else {
            $rosterStartDate = $currentTuesday->copy()->subWeeks(2);
        }

        $user->update([
            'roster_start_date' => $rosterStartDate->format('Y-m-d'),
            'schedule_type'     => 'roster', // Pastikan tipe otomatis terset ke roster
        ]);

        return redirect()->back()->with('success', 'Shift berhasil dikonfirmasi! Rotasi shift Anda akan otomatis berganti setiap hari Selasa.');
    }
    
    public function showInitialShiftForm()
    {
        return view('schedule.initial_shift');
    }

    /**
     * Memperbarui Pengaturan Tipe Jadwal Kerja (Normal / Roster)
     */
    public function updateSchedule(Request $request)
    {
        $request->validate([
            'schedule_type'     => 'required|in:normal,roster',
            'normal_work_days'  => 'nullable|array',
            'normal_check_in'   => 'nullable|string',
            'normal_check_out'  => 'nullable|string',
            'roster_start_date' => 'nullable|date',
        ]);

        $updateData = [
            'schedule_type' => $request->schedule_type,
        ];

        // Jika memilih tipe Normal, simpan detail hari & jam kerjanya
        if ($request->schedule_type === 'normal') {
            if ($request->has('normal_work_days')) {
                $updateData['normal_work_days'] = $request->normal_work_days;
            }
            if ($request->filled('normal_check_in')) {
                $updateData['normal_check_in'] = $request->normal_check_in;
            }
            if ($request->filled('normal_check_out')) {
                $updateData['normal_check_out'] = $request->normal_check_out;
            }
        } 
        // Jika memilih Roster dan ada input roster_start_date
        elseif ($request->schedule_type === 'roster' && $request->filled('roster_start_date')) {
            $updateData['roster_start_date'] = $request->roster_start_date;
        }

        $request->user()->update($updateData);

        return redirect()->back()->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    /**
     * Merekam Vektor Wajah (Descriptor) Karyawan
     */
    public function registerFace(Request $request)
    {
        $request->validate([
            'face_descriptor' => 'required',
        ]);

        $request->user()->update([
            'face_descriptor' => $request->face_descriptor,
        ]);

        return response()->json(['message' => 'Perekaman wajah berhasil disimpan!']);
    }
}