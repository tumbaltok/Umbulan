<?php

namespace Tests\Feature;

use App\Jobs\SendPushNotification;
use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use App\Models\User\UserDevice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiNativeIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    protected Station $station;
    protected Role $roleSupervisor;
    protected Role $roleKaryawan;
    protected JenisCuti $jenisCutiTahunan;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Storage::fake('public');

        $this->station = Station::firstOrCreate(
            ['kode_stasiun' => 'API-ST-01'],
            [
                'name' => 'Stasiun API Test',
                'type' => 'stasiun',
            ]
        );

        $this->roleSupervisor = Role::create([
            'role_name' => 'Supervisor API',
            'description' => 'Approver tahap 1',
            'approval_rules' => [
                'cuti' => ['levels' => 1, 'approver_1_role_id' => null, 'approver_2_role_id' => null],
                'car' => ['levels' => 1, 'approver_1_role_id' => null, 'approver_2_role_id' => null],
                'mpr' => ['levels' => 1, 'approver_1_role_id' => null, 'approver_2_role_id' => null],
            ],
        ]);

        $this->roleKaryawan = Role::create([
            'role_name' => 'Karyawan API',
            'description' => 'Staff API',
            'parent_role_id' => $this->roleSupervisor->id,
            'approval_rules' => [
                'cuti' => ['levels' => 1, 'approver_1_role_id' => $this->roleSupervisor->id, 'approver_2_role_id' => null],
                'car' => ['levels' => 1, 'approver_1_role_id' => $this->roleSupervisor->id, 'approver_2_role_id' => null],
                'mpr' => ['levels' => 1, 'approver_1_role_id' => $this->roleSupervisor->id, 'approver_2_role_id' => null],
            ],
        ]);

        $this->jenisCutiTahunan = JenisCuti::firstOrCreate(
            ['name_cuti' => 'Cuti Tahunan'],
            [
                'parent_id' => null,
                'apakah_wajib_dokumen' => false,
                'butuh_surat_dokter' => false,
            ]
        );
    }

    public function test_users_table_has_no_phone_columns_and_fcm_tables_exist(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'phone_number'));
        $this->assertFalse(Schema::hasColumn('users', 'phone_verified_at'));
        $this->assertFalse(Schema::hasColumn('users', 'phone_otp'));
        $this->assertFalse(Schema::hasColumn('users', 'phone_otp_expires_at'));

        $this->assertTrue(Schema::hasTable('user_devices'));
        $this->assertTrue(Schema::hasTable('notifications'));
    }

    public function test_api_register_login_fcm_token_email_otp_and_logout(): void
    {
        // 1. Master data endpoint publik
        $this->getJson('/api/master-data')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'data' => ['stations', 'genders', 'roles', 'jenis_cutis'],
            ]);

        // 2. Registrasi APK Native tanpa nomor telepon
        $registerRes = $this->postJson('/api/register', [
            'name' => 'Karyawan Mobile',
            'nip' => 'APK-001',
            'email' => 'mobile@umbulan.co.id',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'station_id' => $this->station->id,
            'gender_id' => 1,
            'schedule_type' => 'normal',
            'device_name' => 'Pixel 8 Pro',
            'fcm_token' => 'fcm-token-initial-123',
            'platform' => 'android',
        ]);

        $registerRes->assertStatus(201)
            ->assertJsonPath('data.user.email', 'mobile@umbulan.co.id')
            ->assertJsonPath('data.user.completion_status.email_verified', false)
            ->assertJsonPath('data.user.completion_status.signature_set', false)
            ->assertJsonPath('data.user.completion_status.schedule_set', true)
            ->assertJsonPath('data.user.completion_status.is_complete', false);

        $token = $registerRes->json('data.token');
        $this->assertNotEmpty($token);

        $user = User::where('email', 'mobile@umbulan.co.id')->firstOrFail();
        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'fcm-token-initial-123',
            'platform' => 'android',
        ]);

        // 3. Verifikasi email OTP via API menggunakan cache OTP
        Cache::put("email_verify_otp_{$user->id}", [
            'hash' => Hash::make('654321'),
            'expires_at' => now()->addMinutes(10)->timestamp,
            'attempts' => 0,
        ], now()->addMinutes(10));

        $this->withToken($token)
            ->postJson('/api/email/verify-otp', ['otp' => '654321'])
            ->assertStatus(200)
            ->assertJsonPath('data.user.completion_status.email_verified', true);

        // 4. Update FCM Token & cek /api/me
        $this->withToken($token)
            ->postJson('/api/devices/fcm-token', [
                'fcm_token' => 'fcm-token-updated-456',
                'device_name' => 'Xiaomi 14',
                'platform' => 'android',
            ])
            ->assertStatus(200);

        $this->assertEquals(2, UserDevice::where('user_id', $user->id)->count());

        // 5. Login ulang dengan email + password + fcm_token
        $loginRes = $this->postJson('/api/login', [
            'email' => 'mobile@umbulan.co.id',
            'password' => 'Password123!',
            'fcm_token' => 'fcm-token-updated-456',
            'device_name' => 'Xiaomi 14',
            'platform' => 'android',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', 'mobile@umbulan.co.id');

        $loginToken = $loginRes->json('data.token');

        // 6. Logout mencabut token & menghapus device fcm_token terkait
        $this->withToken($loginToken)
            ->postJson('/api/logout', ['fcm_token' => 'fcm-token-updated-456'])
            ->assertStatus(200);

        $this->assertDatabaseMissing('user_devices', [
            'user_id' => $user->id,
            'fcm_token' => 'fcm-token-updated-456',
        ]);
    }

    public function test_api_submission_guard_push_notification_inbox_and_approval_flow(): void
    {
        $supervisor = User::create([
            'name' => 'Atasan API',
            'email' => 'atasan.api@umbulan.co.id',
            'password' => bcrypt('Password123!'),
            'role_id' => $this->roleSupervisor->id,
            'level' => 2,
            'gender' => 'Laki-laki',
            'gender_id' => 1,
            'station_id' => $this->station->id,
            'schedule_type' => 'normal',
            'email_verified_at' => now(),
            'signature' => 'signatures/atasan.png',
        ]);
        $supervisor->roles()->syncWithoutDetaching([$this->roleSupervisor->id => ['is_primary' => true]]);

        $karyawan = User::create([
            'name' => 'Pemohon API',
            'email' => 'pemohon.api@umbulan.co.id',
            'password' => bcrypt('Password123!'),
            'role_id' => $this->roleKaryawan->id,
            'level' => 3,
            'gender' => 'Laki-laki',
            'gender_id' => 1,
            'station_id' => $this->station->id,
            'schedule_type' => 'normal',
            'email_verified_at' => now(),
            'signature' => null, // Belum lengkap!
        ]);
        $karyawan->roles()->syncWithoutDetaching([$this->roleKaryawan->id => ['is_primary' => true]]);

        SaldoCuti::updateOrCreate(
            [
                'user_id' => $karyawan->id,
                'jenis_cuti_id' => $this->jenisCutiTahunan->id,
                'tahun' => (int) date('Y'),
            ],
            [
                'kuota_awal' => 12,
                'sisa_saldo' => 12,
            ]
        );

        $karyawanToken = $karyawan->createToken('apk-pemohon')->plainTextToken;

        // 1. Akun belum lengkap diblokir middleware dengan JSON 403
        $this->withToken($karyawanToken)
            ->postJson('/api/cuti', [
                'jenis_cuti_id' => $this->jenisCutiTahunan->id,
                'tanggal_mulai' => Carbon::now('Asia/Jakarta')->nextWeekday()->toDateString(),
                'tanggal_selesai' => Carbon::now('Asia/Jakarta')->nextWeekday()->toDateString(),
                'alasan_cuti' => 'Keperluan keluarga',
            ])
            ->assertStatus(403)
            ->assertJsonPath('completion_status.is_complete', false);

        // 2. Lengkapi tanda tangan digital via API
        $dummyBase64Png = 'data:image/png;base64,' . base64_encode('fake-png-bytes');
        $this->withToken($karyawanToken)
            ->postJson('/api/user/signature', ['signature_base64' => $dummyBase64Png])
            ->assertStatus(200);

        $karyawan->refresh();
        $this->assertTrue($karyawan->isAccountComplete());
        $this->app['auth']->forgetGuards();

        // 3. Cek dashboard menunjukkan semua 3 syarat selesai
        $this->withToken($karyawanToken)
            ->getJson('/api/dashboard')
            ->assertStatus(200)
            ->assertJsonPath('data.is_account_complete', true)
            ->assertJsonPath('data.completion_status.email_verified', true)
            ->assertJsonPath('data.completion_status.signature_set', true)
            ->assertJsonPath('data.completion_status.schedule_set', true)
            ->assertJsonPath('data.completion_status.is_complete', true);

        // 4. Ajukan Cuti, CAR, dan MPR saat akun lengkap -> 201 + Push Notification tersimpan ke DB
        $weekday = Carbon::now('Asia/Jakarta')->next(Carbon::MONDAY);

        $cutiRes = $this->withToken($karyawanToken)
            ->postJson('/api/cuti', [
                'jenis_cuti_id' => $this->jenisCutiTahunan->id,
                'tanggal_mulai' => $weekday->toDateString(),
                'tanggal_selesai' => $weekday->toDateString(),
                'alasan_cuti' => 'Cuti tahunan uji coba API',
            ]);
        $cutiRes->assertStatus(201);
        $cutiId = $cutiRes->json('data.id');

        $this->withToken($karyawanToken)
            ->postJson('/api/car', [
                'alasan_pembelian' => 'Pembelian sparepart pompa',
                'items' => [
                    ['nama_barang' => 'Seal Pompa', 'jumlah' => 2, 'satuan' => 'PCS', 'estimasi_harga' => 150000],
                ],
            ])
            ->assertStatus(201);

        $this->withToken($karyawanToken)
            ->postJson('/api/mpr', [
                'priority' => 'Urgent',
                'keperluan_urgensi' => 'Kebutuhan operasional darurat',
                'items' => [
                    ['nama_barang' => 'Klorin Tab', 'jumlah' => 5, 'satuan' => 'DRUM', 'estimasi_harga' => 250000],
                ],
            ])
            ->assertStatus(201);

        // Pastikan Supervisor menerima notifikasi di kotak masuk aplikasi
        $this->app['auth']->forgetGuards();
        $supervisorToken = $supervisor->createToken('apk-atasan')->plainTextToken;
        $notifRes = $this->withToken($supervisorToken)
            ->getJson('/api/notifications');

        $notifRes->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, $notifRes->json('data.unread_count'));

        $firstNotifId = $notifRes->json('data.items.0.id');
        $this->withToken($supervisorToken)
            ->postJson("/api/notifications/{$firstNotifId}/read")
            ->assertStatus(200);

        $this->withToken($supervisorToken)
            ->postJson('/api/notifications/read-all')
            ->assertStatus(200)
            ->assertJsonPath('data.unread_count', 0);

        // 5. Atasan melihat pending approvals dan menyetujui cuti
        $this->withToken($supervisorToken)
            ->getJson('/api/approvals/pending')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.cuti');

        $this->withToken($supervisorToken)
            ->postJson("/api/approvals/cuti/{$cutiId}/process", [
                'action' => 'approve',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status_akhir', 'approved');

        // Pemohon mendapatkan push notification hasil approval
        $this->app['auth']->forgetGuards();
        $this->withToken($karyawanToken)
            ->getJson('/api/notifications')
            ->assertStatus(200)
            ->assertJsonPath('data.unread_count', 1);
    }

    public function test_api_forgot_password_via_email_otp(): void
    {
        $user = User::create([
            'name' => 'Reset User',
            'nip' => 'RST-999',
            'email' => 'reset@umbulan.co.id',
            'password' => bcrypt('OldPassword123!'),
            'station_id' => $this->station->id,
            'gender' => 'Laki-laki',
            'gender_id' => 1,
            'schedule_type' => 'normal',
        ]);

        $this->postJson('/api/forgot-password/send-otp', [
            'identity' => 'RST-999',
        ])->assertStatus(200);

        $this->assertTrue(Cache::has("pwd_reset_otp_{$user->id}"));

        // Simulasikan OTP yang diketahui pada cache untuk verifikasi endpoint reset
        Cache::put("pwd_reset_otp_{$user->id}", [
            'hash' => Hash::make('112233'),
            'expires_at' => now()->addMinutes(5)->timestamp,
            'attempts' => 0,
            'channel' => 'email',
        ], now()->addMinutes(10));

        $this->postJson('/api/forgot-password/reset', [
            'identity' => 'reset@umbulan.co.id',
            'otp' => '112233',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
    }
}
