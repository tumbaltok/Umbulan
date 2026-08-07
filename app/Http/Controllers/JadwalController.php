<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function setInitialShift(Request $request)
    {
        $request->validate([
            'current_shift' => 'required|in:pagi,malam,libur',
        ]);

        $user = $request->user();
        $today = Carbon::today();

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
        ]);

        return redirect()->back()->with('success', 'Shift berhasil dikonfirmasi! Rotasi shift Anda akan otomatis berganti setiap hari Selasa.');
    }
}