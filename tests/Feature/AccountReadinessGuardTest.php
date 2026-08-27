<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountReadinessGuardTest extends TestCase
{
    public function test_incomplete_user_sees_banner_with_5_items_and_gets_blocked()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone_number' => '081234567890',
        ]);
        $user->email_verified_at = now();
        $user->phone_verified_at = now();
        $user->face_descriptor = null; // Biometrik belum
        $user->signature = null;       // TTD belum
        $user->schedule_type = 'normal';

        // 1. Cek evaluasi model
        $status = $user->getAccountCompletionStatus();
        $this->assertTrue($status['email_verified']);
        $this->assertTrue($status['phone_verified']);
        $this->assertFalse($status['face_registered']);
        $this->assertFalse($status['signature_set']);
        $this->assertTrue($status['schedule_set']);
        $this->assertFalse($status['is_complete']);
        $this->assertFalse($user->isAccountComplete());

        // 2. Cek middleware logic
        $middleware = new \App\Http\Middleware\EnsureAccountIsComplete();
        $request = \Illuminate\Http\Request::create('/cuti/ajukan', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function () {
            return new \Symfony\Component\HttpFoundation\Response('OK');
        });

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(session('error'), 'Akses Ditolak: Anda wajib melengkapi verifikasi email, nomor WhatsApp, biometrik wajah, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
    }

    public function test_complete_user_passes_middleware()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone_number' => '081234567890',
        ]);
        $user->email_verified_at = now();
        $user->phone_verified_at = now();
        $user->face_descriptor = [0.1, 0.2];
        $user->signature = 'signatures/dummy.png';
        $user->schedule_type = 'normal';

        $this->assertTrue($user->isAccountComplete());
        $status = $user->getAccountCompletionStatus();
        $this->assertTrue($status['is_complete']);

        $middleware = new \App\Http\Middleware\EnsureAccountIsComplete();
        $request = \Illuminate\Http\Request::create('/cuti/ajukan', 'GET');
        $request->setUserResolver(fn () => $user);

        $response = $middleware->handle($request, function () {
            return new \Symfony\Component\HttpFoundation\Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_banner_renders_correct_5_card_order()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
            'phone_number' => '081234567890',
        ]);
        $user->email_verified_at = now();
        $user->phone_verified_at = now();
        $user->face_descriptor = null;
        $user->signature = null;
        $user->schedule_type = 'normal';

        auth()->setUser($user);

        // Ambil potongan banner dari dashboardindex.blade.php
        $viewContent = file_get_contents(resource_path('views/dashboard/dashboardindex.blade.php'));
        $startPos = strpos($viewContent, '{{-- BANNER SISTEM PERINGATAN KELENGKAPAN AKUN');
        $endPos = strpos($viewContent, '{{-- Statistik Ringkasan --}}');
        $bannerBlade = substr($viewContent, $startPos, $endPos - $startPos);

        $html = \Illuminate\Support\Facades\Blade::render($bannerBlade);

        $this->assertStringContainsString('Peringatan Kelengkapan Akun Karyawan', $html);
        $this->assertStringContainsString('3/5 Syarat Selesai (60%)', $html);

        // Verifikasi urutan kemunculan teks kartu:
        $posEmail = strpos($html, 'Verifikasi Email');
        $posPhone = strpos($html, 'No. WhatsApp');
        $posFace = strpos($html, 'Biometrik Wajah');
        $posSig = strpos($html, 'Tanda Tangan Digital');
        $posSchedule = strpos($html, 'Jadwal Kerja');

        $this->assertNotFalse($posEmail);
        $this->assertNotFalse($posPhone);
        $this->assertNotFalse($posFace);
        $this->assertNotFalse($posSig);
        $this->assertNotFalse($posSchedule);

        // Urutan baku: Email < Phone < Face < Sig < Schedule
        $this->assertTrue($posEmail < $posPhone, 'Urutan: Email harus sebelum WhatsApp');
        $this->assertTrue($posPhone < $posFace, 'Urutan: WhatsApp harus sebelum Biometrik Wajah');
        $this->assertTrue($posFace < $posSig, 'Urutan: Biometrik Wajah harus sebelum TTD');
        $this->assertTrue($posSig < $posSchedule, 'Urutan: TTD harus sebelum Jadwal Kerja');
    }
}
