<?php

namespace App\Services;

use App\Models\Holiday\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * Cache TTL: 24 jam (86400 detik)
     */
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * Master Baseline Cadangan Resmi Hari Libur Nasional & Cuti Bersama SKB 3 Menteri
     * Digunakan sebagai pertahanan terakhir jika database kosong dan jaringan internet offline.
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
     * Ambil Seluruh Data Hari Libur Nasional & Cuti Bersama untuk Tahun Tertentu.
     * Menggunakan Database 'holidays' sebagai sumber utama.
     * Jika database untuk tahun tersebut masih kosong, sistem otomatis menjalankan auto-sync.
     *
     * @return array<string, string> Format: ['YYYY-MM-DD' => 'Nama Libur', ...]
     */
    public function getNationalHolidays(int|string $year): array
    {
        $yearInt = (int) $year;
        $cacheKey = "national_holidays_id_{$yearInt}";

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($yearInt) {
            try {
                // 1. Cek apakah database sudah memiliki data hari libur untuk tahun ini
                $dbHolidays = Holiday::forYear($yearInt)
                    ->orderBy('holiday_date')
                    ->get();

                // 2. Jika database kosong untuk tahun ini, lakukan auto-sync otomatis
                if ($dbHolidays->isEmpty()) {
                    $this->syncYear($yearInt);
                    $dbHolidays = Holiday::forYear($yearInt)
                        ->orderBy('holiday_date')
                        ->get();
                }

                $holidays = [];
                foreach ($dbHolidays as $holiday) {
                    $dateStr = $holiday->holiday_date instanceof Carbon 
                        ? $holiday->holiday_date->format('Y-m-d') 
                        : substr((string) $holiday->holiday_date, 0, 10);
                    $holidays[$dateStr] = $holiday->holiday_name;
                }

                // 3. Jika database masih tetap kosong (misal kegagalan network total dan tahun belum ada di DB),
                // gunakan master baseline darurat jika tersedia
                if (empty($holidays) && isset(self::OFFICIAL_INDONESIA_HOLIDAYS[(string) $yearInt])) {
                    $holidays = self::OFFICIAL_INDONESIA_HOLIDAYS[(string) $yearInt];
                }

                ksort($holidays);
                return $holidays;

            } catch (\Throwable $e) {
                Log::warning("HolidayService: Gagal memuat dari database, fallback ke baseline. Error: " . $e->getMessage());
                return self::OFFICIAL_INDONESIA_HOLIDAYS[(string) $yearInt] ?? [];
            }
        });
    }

    /**
     * Sinkronisasi Data Hari Libur untuk Tahun Tertentu dari Sumber Pemerintah / Mirror Dataset
     *
     * @param int|string $year
     * @return array Ringkasan hasil sinkronisasi
     */
    public function syncYear(int|string $year): array
    {
        $yearInt = (int) $year;
        $syncedCount = 0;
        $fetchedHolidays = [];

        // 1. Ambil dari Mirror Dataset SKB 3 Menteri Indonesia (guangrei/APIHariLibur_V2)
        try {
            $response = Http::timeout(4)->get('https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/master/calendar.min.json');
            if ($response->successful()) {
                $calendarData = $response->json();
                if (is_array($calendarData)) {
                    foreach ($calendarData as $dateKey => $item) {
                        if (str_starts_with($dateKey, (string) $yearInt)) {
                            $name = null;
                            if (isset($item['summary']) && is_array($item['summary']) && !empty($item['summary'])) {
                                $name = $item['summary'][0];
                            } elseif (isset($item['name'])) {
                                $name = $item['name'];
                            }

                            if ($name) {
                                $isCuti = str_contains(strtolower($name), 'cuti bersama');
                                $fetchedHolidays[$dateKey] = [
                                    'name' => $name,
                                    'is_cuti_bersama' => $isCuti,
                                    'source' => 'government_sync',
                                ];
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info("HolidayService: Mirror SKB 3 Menteri tidak terjangkau ({$e->getMessage()}), mencoba fallback...");
        }

        // 2. Gabungkan dengan Baseline Resmi jika tahun tersebut terdaftar
        if (isset(self::OFFICIAL_INDONESIA_HOLIDAYS[(string) $yearInt])) {
            foreach (self::OFFICIAL_INDONESIA_HOLIDAYS[(string) $yearInt] as $dateKey => $name) {
                if (!isset($fetchedHolidays[$dateKey])) {
                    $isCuti = str_contains(strtolower($name), 'cuti bersama');
                    $fetchedHolidays[$dateKey] = [
                        'name' => $name,
                        'is_cuti_bersama' => $isCuti,
                        'source' => 'system_baseline',
                    ];
                }
            }
        }

        // 3. Fallback sekunder: date.nager.at API jika belum ada sama sekali
        if (empty($fetchedHolidays)) {
            try {
                $response = Http::timeout(3)->get("https://date.nager.at/api/v3/PublicHolidays/{$yearInt}/ID");
                if ($response->successful()) {
                    foreach ($response->json() as $item) {
                        $dateKey = Carbon::parse($item['date'])->format('Y-m-d');
                        $name = $item['localName'] ?? $item['name'];
                        if (!isset($fetchedHolidays[$dateKey])) {
                            $fetchedHolidays[$dateKey] = [
                                'name' => $name,
                                'is_cuti_bersama' => false,
                                'source' => 'government_sync',
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("HolidayService: API Nager gagal diakses. Error: " . $e->getMessage());
            }
        }

        // 4. Simpan ke database tanpa menimpa data buatan manual admin ('manual_admin')
        try {
            foreach ($fetchedHolidays as $dateKey => $data) {
                $existing = Holiday::where('holiday_date', $dateKey)->first();

                if ($existing && $existing->source === 'manual_admin') {
                    // Jangan timpa modifikasi manual dari admin
                    continue;
                }

                Holiday::updateOrCreate(
                    ['holiday_date' => $dateKey],
                    [
                        'holiday_name' => $data['name'],
                        'holiday_year' => $yearInt,
                        'is_cuti_bersama' => $data['is_cuti_bersama'],
                        'source' => $data['source'],
                    ]
                );
                $syncedCount++;
            }
        } catch (\Throwable $e) {
            Log::error("HolidayService: Gagal menyimpan data sinkronisasi ke database. Error: " . $e->getMessage());
        }

        // Bersihkan cache agar data terbaru langsung aktif
        $this->clearYearCache($yearInt);

        return [
            'year' => $yearInt,
            'total_fetched' => count($fetchedHolidays),
            'synced_count' => $syncedCount,
        ];
    }

    /**
     * Hapus cache kalender untuk tahun tertentu
     */
    public function clearYearCache(int|string $year): void
    {
        Cache::forget("national_holidays_id_{(int)$year}");
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
