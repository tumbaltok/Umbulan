<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Menampilkan kutipan inspiratif
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset saldo cuti haid bulanan otomatis setiap tanggal 1 pukul 00:00 WIB
Schedule::command('saldo:reset-haid')->monthlyOn(1, '00:00');

// Reset saldo cuti tahunan otomatis setiap tanggal 1 Januari pukul 00:00 WIB
Schedule::command('saldo:reset-tahunan')->yearlyOn(1, 1, '00:00');

// Pengingat WhatsApp berkala (setiap 10 menit) untuk pengajuan pending dengan proteksi jam kerja
Schedule::command('pengajuan:followup-wa')
    ->everyTenMinutes()
    ->timezone('Asia/Jakarta');

