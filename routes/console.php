<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Perintah akan dijalankan otomatis oleh server setiap tanggal 1 awal bulan
Schedule::command('saldo:reset-haid')->monthlyOn(1, '00:00');

// Dijalankan otomatis setiap tanggal 1 Januari jam 00:00 tengah malam
Schedule::command('saldo:reset-tahunan')->yearly();
