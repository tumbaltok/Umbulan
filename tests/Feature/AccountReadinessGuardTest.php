<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountReadinessGuardTest extends TestCase
{
    public function test_incomplete_user_sees_banner_with_3_items_and_gets_blocked()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
        ]);
        $user->email_verified_at = now();
        $user->signature = null;       // TTD belum
        $user->schedule_type = 'reguler_5_hari';

        // 1. Cek evaluasi model
        $status = $user->getAccountCompletionStatus();
        $this->assertTrue($status['email_verified']);
        $this->assertArrayNotHasKey('face_registered', $status);
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
        $this->assertEquals(session('error'), 'Akses Ditolak: Anda wajib melengkapi verifikasi email, tanda tangan digital (TTD), dan jadwal kerja sebelum dapat membuat pengajuan.');
    }

    public function test_complete_user_passes_middleware()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
        ]);
        $user->email_verified_at = now();
        $user->signature = 'signatures/dummy.png';
        $user->schedule_type = 'reguler_5_hari';

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

    public function test_banner_renders_correct_3_card_order()
    {
        $user = new User([
            'id' => 1,
            'name' => 'User Test',
            'email' => 'user@test.com',
        ]);
        $user->email_verified_at = now();
        $user->signature = null;
        $user->schedule_type = 'reguler_5_hari';

        auth()->setUser($user);

        // Ambil potongan banner dari dashboardindex.blade.php
        $viewContent = file_get_contents(resource_path('views/dashboard/dashboardindex.blade.php'));
        $startPos = strpos($viewContent, '{{-- BANNER SISTEM PERINGATAN KELENGKAPAN AKUN');
        $endPos = strpos($viewContent, '{{-- Statistik Ringkasan --}}');
        $bannerBlade = substr($viewContent, $startPos, $endPos - $startPos);

        $html = \Illuminate\Support\Facades\Blade::render($bannerBlade);

        $this->assertStringContainsString('Peringatan Kelengkapan Akun Karyawan', $html);
        $this->assertStringContainsString('2/3 Syarat Selesai (67%)', $html);
        $this->assertStringNotContainsString('Biometrik Wajah', $html);

        // Verifikasi urutan kemunculan teks kartu:
        $posEmail = strpos($html, 'Verifikasi Email');
        $posSig = strpos($html, 'Tanda Tangan Digital');
        $posSchedule = strpos($html, 'Jadwal Kerja');

        $this->assertNotFalse($posEmail);
        $this->assertNotFalse($posSig);
        $this->assertNotFalse($posSchedule);

        // Urutan baku: Email < Sig < Schedule
        $this->assertTrue($posEmail < $posSig, 'Urutan: Email harus sebelum TTD');
        $this->assertTrue($posSig < $posSchedule, 'Urutan: TTD harus sebelum Jadwal Kerja');
    }
}
