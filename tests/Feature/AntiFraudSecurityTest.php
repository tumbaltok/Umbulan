<?php

namespace Tests\Feature;

use App\Http\Controllers\Car\PersetujuanCarController;
use App\Http\Controllers\Cuti\PersetujuanCutiController;
use App\Http\Controllers\Mpr\PersetujuanMprController;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\User;
use App\Traits\CutiHelperTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class AntiFraudSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mpr_rejection_requires_notes()
    {
        $approver = User::where('level', 2)->first();
        $this->actingAs($approver);

        $controller = app(PersetujuanMprController::class);

        $request = Request::create('/persetujuan/mpr/999999/proses', 'POST', [
            'tindakan' => 'rejected',
            'catatan_penolakan' => '', // kosong
        ]);

        $response = $controller->prosesPersetujuan($request, 999999);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals('Catatan penolakan wajib diisi saat menolak pengajuan MPR.', session('error'));
    }

    public function test_anti_collusion_blocks_approver_tahap_1_from_approving_tahap_2_cuti()
    {
        $pemohon = User::where('level', 2)->first();
        $approver = User::where('level', 2)->where('id', '!=', $pemohon->id)->first();

        $pengajuan = PengajuanCuti::create([
            'user_id' => $pemohon->id,
            'jenis_cuti_id' => 1,
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(6)->format('Y-m-d'),
            'total_hari' => 2,
            'alasan_cuti' => 'Keperluan keluarga',
            'status_tahap_1' => 'approved',
            'approver_tahap_1_id' => $approver->id,
            'status_tahap_2' => 'pending',
            'status_akhir' => 'pending',
        ]);

        $this->actingAs($approver);
        $controller = app(PersetujuanCutiController::class);

        $request = Request::create("/persetujuan/cuti/{$pengajuan->id}/proses", 'POST', [
            'tindakan' => 'approved',
        ]);

        $response = $controller->prosesPersetujuan($request, $pengajuan->id);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('Anti-Collusion Protection', session('error'));
    }

    public function test_anti_collusion_blocks_approver_tahap_1_from_approving_tahap_2_car()
    {
        $pemohon = User::where('level', 2)->first();
        $approver = User::where('level', 2)->where('id', '!=', $pemohon->id)->first();

        $pengajuan = PengajuanCar::create([
            'user_id' => $pemohon->id,
            'nomor_car' => 'CAR-TEST-' . uniqid(),
            'tujuan' => 'Pembelian operasional',
            'tanggal' => now()->format('Y-m-d'),
            'total_biaya' => 500000,
            'status_tahap_1' => 'approved',
            'approver_tahap_1_id' => $approver->id,
            'status_tahap_2' => 'pending',
            'status_akhir' => 'pending',
        ]);

        $this->actingAs($approver);
        $controller = app(PersetujuanCarController::class);

        $request = Request::create("/persetujuan/car/{$pengajuan->id}/proses", 'POST', [
            'tindakan' => 'approved',
        ]);

        $response = $controller->prosesPersetujuan($request, $pengajuan->id);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('Anti-Collusion Protection', session('error'));
    }

    public function test_anti_collusion_blocks_approver_tahap_1_from_approving_tahap_2_mpr()
    {
        $pemohon = User::where('level', 2)->first();
        $approver = User::where('level', 2)->where('id', '!=', $pemohon->id)->first();

        $pengajuan = PengajuanMpr::create([
            'user_id' => $pemohon->id,
            'nomor_mpr' => 'MPR-TEST-' . uniqid(),
            'tanggal_pengajuan' => now()->format('Y-m-d'),
            'keperluan_urgensi' => 'Kebutuhan sparepart mendesak',
            'status_tahap_1' => 'approved',
            'approver_tahap_1_id' => $approver->id,
            'status_tahap_2' => 'pending',
            'status_akhir' => 'pending',
        ]);

        $this->actingAs($approver);
        $controller = app(PersetujuanMprController::class);

        $request = Request::create("/persetujuan/mpr/{$pengajuan->id}/proses", 'POST', [
            'tindakan' => 'approved',
        ]);

        $response = $controller->prosesPersetujuan($request, $pengajuan->id);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('Anti-Collusion Protection', session('error'));
    }

    public function test_cuti_balance_idempotency_does_not_double_deduct()
    {
        $consumer = new DummyCutiConsumerHelper();

        $pengajuan = new PengajuanCuti();
        $pengajuan->user_id = 1;
        $pengajuan->jenis_cuti_id = 1;
        $pengajuan->tanggal_mulai = now()->format('Y-m-d');
        $pengajuan->tanggal_selesai = now()->format('Y-m-d');
        $pengajuan->total_hari = 1;
        $pengajuan->is_cut_saldo = true; // sudah dipotong sebelumnya

        // Karena is_cut_saldo bernilai true, pemotongan kedua tidak boleh memanggil potongSaldoDatabase
        $consumer->sinkronisasiCutiDanAbsen($pengajuan);
        $this->assertTrue($pengajuan->is_cut_saldo);
        $this->assertFalse($consumer->didCallPotongSaldo);
    }
}

class DummyCutiConsumerHelper
{
    use CutiHelperTrait;

    public bool $didCallPotongSaldo = false;

    public function alurPotongSaldo(int $jenisCutiId, ?int $subCutiId = null): bool
    {
        return true;
    }

    public function potongSaldoDatabase(PengajuanCuti $pengajuan): void
    {
        $this->didCallPotongSaldo = true;
    }
}
