<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Bawaan Laravel: Menampilkan quotes inspiratif
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 1. Reset Saldo Haid otomatis setiap tanggal 1 awal bulan jam 00:00
Schedule::command('saldo:reset-haid')->monthlyOn(1, '00:00');

// 2. Reset Saldo Tahunan otomatis setiap tanggal 1 Januari jam 00:00
Schedule::command('saldo:reset-tahunan')->yearlyOn(1, 1, '00:00');

// 3. JADWAL: Pengingat WhatsApp Berkala untuk Seluruh Pengajuan Pending (Cuti, CAR, MPR)
// Dilengkapi Work Hours Guard (Staf Normal & Staf Roster) serta proteksi anti-spam ganda.
Schedule::command('pengajuan:followup-wa')
    ->everyTenMinutes()
    ->timezone('Asia/Jakarta');

