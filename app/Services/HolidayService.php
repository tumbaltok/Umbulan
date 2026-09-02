<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * Cache TTL: 30 hari
     */
    private const CACHE_TTL_SECONDS = 2592000;

    /**
     * Master Data Resmi Hari Libur Nasional & Cuti Bersama SKB 3 Menteri (Indonesia)
     */
    private const OFFICIAL_INDONESIA_HOLIDAYS = [
        '2025' => [
            '2025-01-01' => 'Tahun Baru 2025 Masehi',
            '2025-01-27' => 'Isra Mi\'raj Nabi Muhammad SAW',
            '2025-01-28' => 'Cuti Bersama Tahun Baru Imlek 2576 Kongzili',
            '2025-01-29' => 'Tahun Baru Imlek 2576 Kongzili',
            '2025-03-28' => 'Cuti Bersama Hari Suci Nyepi (Saka 1947)',
            '2025-03-29' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)',
            '2025-03-31' => 'Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-01' => 'Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-02' => 'Cuti Bersama Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-03' => 'Cuti Bersama Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-04' => 'Cuti Bersama Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-07' => 'Cuti Bersama Hari Raya Idul Fitri 1446 Hijriah',
            '2025-04-18' => 'Wafat Yesus Kristus',
            '2025-04-20' => 'Kebangkitan Yesus Kristus (Paskah)',
            '2025-05-01' => 'Hari Buruh Internasional',
            '2025-05-12' => 'Hari Raya Waisak 2569 BE',
            '2025-05-13' => 'Cuti Bersama Hari Raya Waisak 2569 BE',
            '2025-05-29' => 'Kenaikan Yesus Kristus',
            '2025-05-30' => 'Cuti Bersama Kenaikan Yesus Kristus',
            '2025-06-01' => 'Hari Lahir Pancasila',
            '2025-06-06' => 'Hari Raya Idul Adha 1446 Hijriah',
            '2025-06-09' => 'Cuti Bersama Hari Raya Idul Adha 1446 Hijriah',
            '2025-06-27' => '1 Muharram / Tahun Baru Islam 1447 Hijriah',
            '2025-08-17' => 'Hari Kemerdekaan Republik Indonesia ke-80',
            '2025-09-05' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1447 H)',
            '2025-12-25' => 'Hari Raya Natal',
            '2025-12-26' => 'Cuti Bersama Hari Raya Natal',
        ],
        '2026' => [
            // 1. Libur Nasional & Cuti Bersama Resmi 2026 SKB 3 Menteri
            '2026-01-01' => 'Tahun Baru 2026 Masehi',
            '2026-01-16' => 'Isra Mi\'raj Nabi Muhammad SAW',
            '2026-02-16' => 'Cuti Bersama Tahun Baru Imlek 2577 Kongzili',
            '2026-02-17' => 'Tahun Baru Imlek 2577 Kongzili',
            '2026-03-18' => 'Cuti Bersama Hari Suci Nyepi (Saka 1948)',
            '2026-03-19' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)',
            '2026-03-20' => 'Hari Raya Idul Fitri 1447 Hijriah',
            '2026-03-21' => 'Hari Raya Idul Fitri 1447 Hijriah',
            '2026-03-23' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriah',
            '2026-03-24' => 'Cuti Bersama Hari Raya Idul Fitri 1447 Hijriah',
            '2026-04-03' => 'Wafat Yesus Kristus (Jumat Agung)',
            '2026-04-05' => 'Kebangkitan Yesus Kristus (Paskah)',
            '2026-05-01' => 'Hari Buruh Internasional',
            '2026-05-14' => 'Kenaikan Yesus Kristus',
            '2026-05-15' => 'Cuti Bersama Kenaikan Yesus Kristus',
            '2026-05-27' => 'Hari Raya Idul Adha 1447 Hijriah',
            '2026-05-28' => 'Cuti Bersama Hari Raya Idul Adha 1447 Hijriah',
            '2026-05-31' => 'Hari Raya Waisak 2570 BE',
            '2026-06-01' => 'Hari Lahir Pancasila',
            '2026-06-02' => 'Cuti Bersama Hari Raya Waisak 2570 BE',
            '2026-06-16' => 'Tahun Baru Islam 1448 Hijriah (1 Muharram)',
            '2026-08-17' => 'Hari Kemerdekaan Republik Indonesia ke-81',
            '2026-08-25' => 'Maulid Nabi Muhammad SAW (12 Rabiul Awal 1448 H)',
            '2026-12-24' => 'Cuti Bersama Hari Raya Natal',
            '2026-12-25' => 'Hari Raya Natal',
        ],
        '2027' => [
            '2027-01-01' => 'Tahun Baru 2027 Masehi',
            '2027-01-06' => 'Isra Mi\'raj Nabi Muhammad SAW',
            '2027-02-06' => 'Tahun Baru Imlek 2578 Kongzili',
            '2027-03-09' => 'Hari Raya Idul Fitri 1448 Hijriah',
            '2027-03-10' => 'Hari Raya Idul Fitri 1448 Hijriah',
            '2027-03-26' => 'Wafat Yesus Kristus',
            '2027-04-07' => 'Hari Suci Nyepi (Saka 1949)',
            '2027-05-01' => 'Hari Buruh Internasional',
            '2027-05-06' => 'Kenaikan Yesus Kristus',
            '2027-05-16' => 'Hari Raya Idul Adha 1448 Hijriah',
            '2027-05-20' => 'Hari Raya Waisak 2571 BE',
            '2027-06-01' => 'Hari Lahir Pancasila',
            '2027-06-06' => 'Tahun Baru Islam 1449 Hijriah',
            '2027-08-15' => 'Maulid Nabi Muhammad SAW',
            '2027-08-17' => 'Hari Kemerdekaan Republik Indonesia ke-82',
            '2027-12-25' => 'Hari Raya Natal',
        ],
    ];

    /**
     * Ambil Seluruh Data Hari Libur Nasional & Cuti Bersama untuk Tahun Tertentu
     *
     * @return array<string, string> Format: ['YYYY-MM-DD' => 'Nama Libur', ...]
     */
    public function getNationalHolidays(int|string $year): array
    {
        $yearKey = (string) $year;

        return Cache::remember("national_holidays_id_{$yearKey}", self::CACHE_TTL_SECONDS, function () use ($yearKey) {
            $holidays = self::OFFICIAL_INDONESIA_HOLIDAYS[$yearKey] ?? [];

            // Ambil data tambahan dari API eksternal jika tersedia (timeout 3 detik)
            try {
                $response = Http::timeout(3)->get("https://date.nager.at/api/v3/PublicHolidays/{$yearKey}/ID");
                if ($response->successful()) {
                    foreach ($response->json() as $item) {
                        $date = Carbon::parse($item['date'])->format('Y-m-d');
                        $name = $item['localName'] ?? $item['name'];
                        if (!isset($holidays[$date])) {
                            $holidays[$date] = $name;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Fallback menggunakan master data resmi SKB 3 Menteri jika API gagal
                Log::info("HolidayService: Menggunakan master resmi SKB 3 Menteri untuk tahun {$yearKey}. Info: " . $e->getMessage());
            }

            // Urutkan data berdasarkan kunci tanggal
            ksort($holidays);

            return $holidays;
        });
    }

    /**
     * Cek apakah suatu tanggal adalah Hari Libur Nasional / Tanggal Merah
     */
    public function isNationalHoliday(string|Carbon $date): bool
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $holidays = $this->getNationalHolidays($carbonDate->year);

        return isset($holidays[$carbonDate->format('Y-m-d')]);
    }

    /**
     * Ambil nama hari libur pada tanggal tertentu (atau null jika bukan hari libur)
     */
    public function getHolidayName(string|Carbon $date): ?string
    {
        $carbonDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        $holidays = $this->getNationalHolidays($carbonDate->year);

        return $holidays[$carbonDate->format('Y-m-d')] ?? null;
    }
}
