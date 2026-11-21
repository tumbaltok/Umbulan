<?php

namespace App\Console\Commands;

use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHolidaysCommand extends Command
{
    /**
     * Nama dan signature perintah konsol.
     */
    protected $signature = 'holiday:sync {year? : Tahun yang ingin disinkronkan}';

    /**
     * Deskripsi perintah konsol.
     */
    protected $description = 'Sinkronisasi data Hari Libur Nasional & Cuti Bersama resmi SKB 3 Menteri ke database';

    public function handle(?HolidayService $holidayService = null): int
    {
        $holidayService = $holidayService ?? app(HolidayService::class);
        $inputYear = $this->argument('year');

        if ($inputYear) {
            $years = [(int) $inputYear];
        } else {
            $currentYear = Carbon::now('Asia/Jakarta')->year;
            $years = [$currentYear, $currentYear + 1];
        }

        $this->info("Memulai sinkronisasi data Hari Libur Nasional SKB 3 Menteri...");

        foreach ($years as $year) {
            $this->line("-> Memproses kalender tahun {$year}...");
            $result = $holidayService->syncYear($year);
            $this->info("   [OK] Tahun {$year}: {$result['synced_count']} tanggal berhasil disinkronkan (Total: {$result['total_fetched']}).");
        }

        $this->info("Sinkronisasi hari libur nasional selesai dengan sukses.");

        return Command::SUCCESS;
    }
}
