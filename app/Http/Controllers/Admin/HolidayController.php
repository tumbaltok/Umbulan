<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday\Holiday;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    protected HolidayService $holidayService;

    public function __construct(HolidayService $holidayService)
    {
        $this->holidayService = $holidayService;
    }

    /**
     * Menampilkan daftar hari libur nasional & cuti bersama.
     */
    public function index(Request $request)
    {
        $currentYear = Carbon::now('Asia/Jakarta')->year;
        $selectedYear = (int) $request->input('year', $currentYear);
        $search = $request->input('q');
        $type = $request->input('type'); // 'all', 'national', 'cuti_bersama'

        // Pastikan data tahun terpilih sudah disinkronkan minimal sekali jika kosong
        if (Holiday::forYear($selectedYear)->count() === 0) {
            $this->holidayService->syncYear($selectedYear);
        }

        $query = Holiday::forYear($selectedYear)->orderBy('holiday_date', 'asc');

        if ($search) {
            $query->where('holiday_name', 'like', "%{$search}%");
        }

        if ($type === 'national') {
            $query->where('is_cuti_bersama', false);
        } elseif ($type === 'cuti_bersama') {
            $query->where('is_cuti_bersama', true);
        }

        $holidays = $query->paginate(25)->withQueryString();

        // Daftar tahun yang tersedia untuk filter dropdown
        $dbYears = Holiday::select('holiday_year')
            ->distinct()
            ->orderBy('holiday_year', 'asc')
            ->pluck('holiday_year')
            ->toArray();

        $availableYears = array_unique(array_merge([$currentYear - 1, $currentYear, $currentYear + 1], $dbYears));
        sort($availableYears);

        // Statistik ringkas tahun terpilih
        $totalHolidays = Holiday::forYear($selectedYear)->count();
        $totalNational = Holiday::forYear($selectedYear)->where('is_cuti_bersama', false)->count();
        $totalCutiBersama = Holiday::forYear($selectedYear)->where('is_cuti_bersama', true)->count();
        $totalManual = Holiday::forYear($selectedYear)->where('source', 'manual_admin')->count();

        return view('admin.daftar.holidayindex', compact(
            'holidays',
            'selectedYear',
            'availableYears',
            'search',
            'type',
            'totalHolidays',
            'totalNational',
            'totalCutiBersama',
            'totalManual'
        ));
    }

    /**
     * Menyimpan hari libur manual baru (misal instruksi libur dadakan pemerintah).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => 'required|date|unique:holidays,holiday_date',
            'holiday_name' => 'required|string|max:255',
            'is_cuti_bersama' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ], [
            'holiday_date.required' => 'Tanggal libur wajib diisi.',
            'holiday_date.unique' => 'Tanggal libur ini sudah terdaftar sebelumnya.',
            'holiday_name.required' => 'Nama hari libur wajib diisi.',
        ]);

        $carbonDate = Carbon::parse($validated['holiday_date']);
        $year = $carbonDate->year;

        Holiday::create([
            'holiday_date' => $carbonDate->format('Y-m-d'),
            'holiday_name' => $validated['holiday_name'],
            'holiday_year' => $year,
            'is_cuti_bersama' => (bool) ($validated['is_cuti_bersama'] ?? false),
            'source' => 'manual_admin',
            'description' => $validated['description'] ?? null,
        ]);

        $this->holidayService->clearYearCache($year);

        return redirect()->back()->with('success', "Hari libur '{$validated['holiday_name']}' berhasil ditambahkan ke kalender.");
    }

    /**
     * Memperbarui data hari libur.
     */
    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $validated = $request->validate([
            'holiday_name' => 'required|string|max:255',
            'is_cuti_bersama' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ], [
            'holiday_name.required' => 'Nama hari libur wajib diisi.',
        ]);

        $holiday->update([
            'holiday_name' => $validated['holiday_name'],
            'is_cuti_bersama' => (bool) ($validated['is_cuti_bersama'] ?? false),
            'description' => $validated['description'] ?? null,
        ]);

        $this->holidayService->clearYearCache($holiday->holiday_year);

        return redirect()->back()->with('success', "Data hari libur '{$holiday->holiday_name}' berhasil diperbarui.");
    }

    /**
     * Menghapus hari libur.
     */
    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $name = $holiday->holiday_name;
        $year = $holiday->holiday_year;

        $holiday->delete();
        $this->holidayService->clearYearCache($year);

        return redirect()->back()->with('success', "Hari libur '{$name}' berhasil dihapus dari kalender.");
    }

    /**
     * Memicu sinkronisasi manual dari sumber SKB 3 Menteri resmi.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2020|max:2050',
        ]);

        $currentYear = Carbon::now('Asia/Jakarta')->year;
        $year = (int) $request->input('year', $currentYear);

        $result = $this->holidayService->syncYear($year);

        return redirect()->back()->with('success', "Sinkronisasi SKB 3 Menteri tahun {$year} selesai: {$result['synced_count']} tanggal diperbarui.");
    }
}
