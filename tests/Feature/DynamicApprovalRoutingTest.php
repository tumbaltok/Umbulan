<?php

namespace Tests\Feature;

use App\Models\User\Station;
use App\Models\User\Gender;
use App\Models\Car\PengajuanCar;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Models\User\Role;
use App\Models\User\User;
use App\Services\ApprovalRoutingService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DynamicApprovalRoutingTest extends TestCase
{
    use DatabaseMigrations;
    protected Role $roleOperator;
    protected Role $roleSpv;
    protected Role $roleManager;
    protected Role $roleDirector;
    protected Station $station;
    protected Gender $gender;

    protected User $operatorUser;
    protected User $spvUser;
    protected User $managerUser;
    protected User $directorUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->station = Station::firstOrCreate(['name' => 'Kantor Pusat'], ['kode_stasiun' => 'KP', 'type' => 'kantor']);
        $this->gender = Gender::firstOrCreate(['name' => 'Laki-laki']);

        // 1. Setup Role Hierarchy
        // Director (Top Level) -> Manager -> SPV -> Operator
        $this->roleDirector = Role::firstOrCreate(['role_name' => 'DIRECTOR TEST'], [
            'parent_role_id' => null,
            'approval_rules' => null,
        ]);

        $this->roleManager = Role::firstOrCreate(['role_name' => 'MANAGER TEST'], [
            'parent_role_id' => $this->roleDirector->id,
            'approval_rules' => null,
        ]);

        $this->roleSpv = Role::firstOrCreate(['role_name' => 'SPV TEST'], [
            'parent_role_id' => $this->roleManager->id,
            'approval_rules' => null,
        ]);

        $this->roleOperator = Role::firstOrCreate(['role_name' => 'OPERATOR TEST'], [
            'parent_role_id' => $this->roleSpv->id,
            'approval_rules' => null,
        ]);

        // 2. Setup Users
        $this->directorUser = User::create([
            'name' => 'Director Test',
            'email' => 'director@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->roleDirector->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'phone_number' => '628111111111',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'schedule_type' => 'reguler_5_hari',
            'level' => 1,
        ]);
        $this->directorUser->roles()->syncWithoutDetaching([$this->roleDirector->id => ['is_primary' => true]]);

        $this->managerUser = User::create([
            'name' => 'Manager Test',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->roleManager->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'phone_number' => '628222222222',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'schedule_type' => 'reguler_5_hari',
            'level' => 2,
        ]);
        $this->managerUser->roles()->syncWithoutDetaching([$this->roleManager->id => ['is_primary' => true]]);

        $this->spvUser = User::create([
            'name' => 'SPV Test',
            'email' => 'spv@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->roleSpv->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'phone_number' => '628333333333',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'schedule_type' => 'reguler_5_hari',
            'level' => 2,
        ]);
        $this->spvUser->roles()->syncWithoutDetaching([$this->roleSpv->id => ['is_primary' => true]]);

        $this->operatorUser = User::create([
            'name' => 'Operator Test',
            'email' => 'operator@test.com',
            'password' => bcrypt('password'),
            'role_id' => $this->roleOperator->id,
            'station_id' => $this->station->id,
            'gender_id' => $this->gender->id,
            'phone_number' => '628444444444',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'schedule_type' => 'reguler_5_hari',
            'level' => 3,
        ]);
        $this->operatorUser->roles()->syncWithoutDetaching([$this->roleOperator->id => ['is_primary' => true]]);
    }

    /**
     * Test 1: Fallback ke Atasan Langsung (parent_role_id) jika modul belum diatur khusus.
     */
    public function test_fallback_to_parent_role_when_module_rules_not_configured(): void
    {
        $service = app(ApprovalRoutingService::class);

        // Operator belum memiliki approval_rules di modul cuti
        $route = $service->resolveApprovalRoute($this->operatorUser, 'cuti');

        $this->assertEquals(1, $route['levels']);
        $this->assertEquals($this->roleSpv->id, $route['approver_1_role_id']);
        $this->assertNull($route['approver_2_role_id']);
        $this->assertFalse($route['is_auto_approved']);
        $this->assertEquals('pending', $route['status_tahap_1']);
        $this->assertEquals('not_required', $route['status_tahap_2']);
        $this->assertEquals('pending', $route['status_akhir']);
    }

    /**
     * Test 2: Pengaturan spesifik modul mengesampingkan parent_role_id (Prioritas Aturan Modul).
     */
    public function test_explicit_module_rules_override_parent_role(): void
    {
        // Admin mengatur khusus modul CAR untuk Operator: 2-Step (Step 1: SPV, Step 2: Manager)
        $this->roleOperator->update([
            'approval_rules' => [
                'car' => [
                    'levels' => 2,
                    'approver_1_role_id' => $this->roleSpv->id,
                    'approver_2_role_id' => $this->roleManager->id,
                ],
            ],
        ]);

        $service = app(ApprovalRoutingService::class);
        $route = $service->resolveApprovalRoute($this->operatorUser, 'car');

        $this->assertEquals(2, $route['levels']);
        $this->assertEquals($this->roleSpv->id, $route['approver_1_role_id']);
        $this->assertEquals($this->roleManager->id, $route['approver_2_role_id']);
        $this->assertEquals('pending', $route['status_tahap_1']);
        $this->assertEquals('pending', $route['status_tahap_2']);
        $this->assertEquals('pending', $route['status_akhir']);
    }

    /**
     * Test 3: Top-Level Role (Tanpa Atasan) otomatis Auto-Approved.
     */
    public function test_top_level_role_is_auto_approved(): void
    {
        $service = app(ApprovalRoutingService::class);
        $route = $service->resolveApprovalRoute($this->directorUser, 'cuti');

        $this->assertTrue($route['is_auto_approved']);
        $this->assertEquals('approved', $route['status_tahap_1']);
        $this->assertEquals('not_required', $route['status_tahap_2']);
        $this->assertEquals('approved', $route['status_akhir']);
    }

    /**
     * Test 4: Pencarian Approver Tahap 1 mengembalikan User dengan Role yang tepat.
     */
    public function test_get_approvers_for_stage_returns_correct_users(): void
    {
        $service = app(ApprovalRoutingService::class);
        $approvers = $service->getApproversForStage($this->operatorUser, 'cuti', 1);

        $this->assertCount(1, $approvers);
        $this->assertEquals($this->spvUser->id, $approvers->first()->id);
    }

    /**
     * Test 5: Self-Approval Protection - Pemohon dilarang menyetujui pengajuannya sendiri.
     */
    public function test_self_approval_protection(): void
    {
        $service = app(ApprovalRoutingService::class);

        // SPV mengajukan cuti
        $cuti = new PengajuanCuti([
            'user_id' => $this->spvUser->id,
            'status_tahap_1' => 'pending',
            'status_tahap_2' => 'not_required',
            'status_akhir' => 'pending',
        ]);
        $cuti->user = $this->spvUser;

        // SPV mencoba menyetujui pengajuannya sendiri
        $authCheck = $service->isUserAuthorizedToApprove($this->spvUser, $cuti, 'cuti');

        $this->assertFalse($authCheck['authorized']);
        $this->assertStringContainsString('Self-Approval Protection', $authCheck['reason']);
    }

    /**
     * Test 6: Anti-Collusion Protection - Approver Tahap 1 dilarang memproses Tahap 2.
     */
    public function test_anti_collusion_protection(): void
    {
        $service = app(ApprovalRoutingService::class);

        // Pengajuan 2-Step yang Tahap 1 disetujui oleh SPV
        $cuti = new PengajuanCuti([
            'user_id' => $this->operatorUser->id,
            'status_tahap_1' => 'approved',
            'approver_tahap_1_id' => $this->spvUser->id,
            'status_tahap_2' => 'pending',
            'status_akhir' => 'pending',
        ]);
        $cuti->user = $this->operatorUser;

        // SPV mencoba memproses Tahap 2
        $authCheck = $service->isUserAuthorizedToApprove($this->spvUser, $cuti, 'cuti');

        $this->assertFalse($authCheck['authorized']);
        $this->assertStringContainsString('Anti-Collusion', $authCheck['reason']);
    }

    /**
     * Test 7: applyPendingApprovalScope memfilter antrean pengajuan tepat sasaran sesuai role & tahap.
     */
    public function test_apply_pending_approval_scope_filters_correctly(): void
    {
        $jenisCuti = JenisCuti::where('kode_cuti', 'CT')->first() ?? JenisCuti::first();

        // Operator mengajukan cuti (fallback: SPV adalah Approver 1)
        $cuti = PengajuanCuti::create([
            'user_id' => $this->operatorUser->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'tanggal_mulai' => now()->addDays(2),
            'tanggal_selesai' => now()->addDays(3),
            'total_hari' => 2,
            'alasan_cuti' => 'Liburan',
            'status_tahap_1' => 'pending',
            'status_tahap_2' => 'not_required',
            'status_akhir' => 'pending',
        ]);

        $service = app(ApprovalRoutingService::class);

        // SPV seharusnya melihat 1 antrean cuti
        $spvPendingCount = $service->applyPendingApprovalScope(PengajuanCuti::query(), $this->spvUser, 'cuti')->count();
        $this->assertEquals(1, $spvPendingCount);

        // Operator (pemohon sendiri) TIDAK boleh melihat antrean persetujuannya sendiri
        $opPendingCount = $service->applyPendingApprovalScope(PengajuanCuti::query(), $this->operatorUser, 'cuti')->count();
        $this->assertEquals(0, $opPendingCount);

        // Manager tidak berada di rantai persetujuan tahap 1 ini
        $managerPendingCount = $service->applyPendingApprovalScope(PengajuanCuti::query(), $this->managerUser, 'cuti')->count();
        $this->assertEquals(0, $managerPendingCount);
    }

    /**
     * Test 8: Notifikasi status persetujuan terkirim / masuk antrean job saat pengajuan disetujui / ditolak.
     */
    public function test_status_update_notification_queued_on_approval_and_rejection(): void
    {
        Queue::fake();

        $jenisCuti = JenisCuti::where('kode_cuti', 'CT')->first() ?? JenisCuti::first();

        $cuti = PengajuanCuti::create([
            'user_id' => $this->operatorUser->id,
            'jenis_cuti_id' => $jenisCuti->id,
            'tanggal_mulai' => now()->addDays(5),
            'tanggal_selesai' => now()->addDays(6),
            'total_hari' => 2,
            'alasan_cuti' => 'Urusan keluarga',
            'status_tahap_1' => 'pending',
            'status_tahap_2' => 'not_required',
            'status_akhir' => 'pending',
        ]);

        // Simulasikan login sebagai SPV untuk menyetujui pengajuan
        $this->actingAs($this->spvUser);

        $response = $this->from('/admin/persetujuan/cuti')->post(route('admin.persetujuan.cuti.proses', $cuti->id), [
            'tindakan' => 'approved',
        ]);

        $response->assertSessionHas('success');

        $cuti->refresh();
        $this->assertEquals('approved', $cuti->status_tahap_1);
        $this->assertEquals('approved', $cuti->status_akhir);

        // Pastikan job notifikasi status ter-dispatch ke antrean untuk dikirim via WA
        Queue::assertPushed(\App\Jobs\SendWhatsAppNotification::class, function ($job) {
            return $job->type === 'status_update';
        });
    }

    /**
     * Test 9: RoleController menolak konfigurasi bertingkat 2 jika Approver 1 dan 2 sama.
     */
    public function test_role_controller_rejects_identical_approvers_on_2_step_route(): void
    {
        $this->actingAs($this->directorUser);

        $response = $this->from('/admin/role')->put(route('admin.role.update', $this->roleOperator->id), [
            'role_name' => $this->roleOperator->role_name,
            'cuti_approval_levels' => 2,
            'cuti_approver_1_role_id' => $this->roleSpv->id,
            'cuti_approver_2_role_id' => $this->roleSpv->id, // Sama dengan tahap 1!
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('tidak boleh memiliki role yang sama', session('error'));
    }

    /**
     * Test 10: Halaman antrean persetujuan Cuti, CAR, dan MPR dapat diakses tanpa error undefined variable.
     */
    public function test_admin_persetujuan_index_views_render_successfully(): void
    {
        $this->actingAs($this->spvUser);

        // 1. Cuti
        $responseCuti = $this->get(route('admin.persetujuan.cuti'));
        $responseCuti->assertStatus(200);
        $responseCuti->assertViewIs('admin.persetujuan.persetujuancuti');

        // 2. CAR
        $responseCar = $this->get(route('admin.persetujuan.car'));
        $responseCar->assertStatus(200);
        $responseCar->assertViewIs('admin.persetujuan.persetujuanCar');

        // 3. MPR
        $responseMpr = $this->get(route('admin.persetujuan.mpr'));
        $responseMpr->assertStatus(200);
        $responseMpr->assertViewIs('admin.persetujuan.persetujuanmpr');
    }
}

