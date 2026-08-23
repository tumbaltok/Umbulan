<?php

namespace Database\Seeders;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\Gender;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ==========================================
        // 1. CLEANUP & PREPARE STORAGE DIRECTORIES
        // ==========================================
        Storage::disk('public')->deleteDirectory('profile_photos');
        Storage::disk('public')->deleteDirectory('dokumen_cuti');
        Storage::disk('public')->deleteDirectory('dokumen_cat');
        Storage::disk('public')->deleteDirectory('dokumen_mpr');

        Storage::disk('public')->makeDirectory('profile_photos');
        Storage::disk('public')->makeDirectory('dokumen_cuti');
        Storage::disk('public')->makeDirectory('dokumen_cat');
        Storage::disk('public')->makeDirectory('dokumen_mpr');

        // ==========================================
        // 2. MASTER GENDERS
        // ==========================================
        $pria = Gender::create(['name' => 'Pria']);
        $wanita = Gender::create(['name' => 'Wanita']);

        // ==========================================
        // 3. MASTER STATIONS (4 UTAMA + 18 RUMAH METER)
        // ==========================================
        $stUmbulan = Station::updateOrCreate(['kode_stasiun' => 'UMBULAN'], [
            'name' => 'Stasiun Umbulan', 'type' => 'stasiun',
            'latitude' => -7.7572565, 'longitude' => 112.9314949, 'radius_meters' => 1000,
        ]);
        $stBooster = Station::updateOrCreate(['kode_stasiun' => 'BOOSTER_M'], [
            'name' => 'Stasiun Booster-M', 'type' => 'stasiun',
            'latitude' => -7.5812341, 'longitude' => 112.7212341, 'radius_meters' => 500,
        ]);
        $stSurabaya = Station::updateOrCreate(['kode_stasiun' => 'HO_SBY'], [
            'name' => 'Kantor Surabaya', 'type' => 'kantor',
            'latitude' => -7.2574719, 'longitude' => 112.7520883, 'radius_meters' => 200,
        ]);
        $stJakarta = Station::updateOrCreate(['kode_stasiun' => 'HO_JKT'], [
            'name' => 'Kantor Jakarta', 'type' => 'kantor',
            'latitude' => -6.2087634, 'longitude' => 106.845599, 'radius_meters' => 200,
        ]);

        $listRumahMeter = [
            ['kode' => 'RM_01', 'name' => 'Winongan', 'lat' => -7.7210, 'long' => 112.9520],
            ['kode' => 'RM_02', 'name' => 'Pohjentrek', 'lat' => -7.6710, 'long' => 112.8910],
            ['kode' => 'RM_03', 'name' => 'Pleret', 'lat' => -7.6510, 'long' => 112.8810],
            ['kode' => 'RM_04', 'name' => 'PIER', 'lat' => -7.6010, 'long' => 112.8310],
            ['kode' => 'RM_05', 'name' => 'Bangil', 'lat' => -7.5910, 'long' => 112.7810],
            ['kode' => 'RM_06', 'name' => 'Gempol', 'lat' => -7.5810, 'long' => 112.7110],
            ['kode' => 'RM_07', 'name' => 'Porong PDAB', 'lat' => -7.5410, 'long' => 112.7010],
            ['kode' => 'RM_08', 'name' => 'Porong PDAM', 'lat' => -7.5380, 'long' => 112.7000],
            ['kode' => 'RM_09', 'name' => 'Tanggulangin', 'lat' => -7.5010, 'long' => 112.7110],
            ['kode' => 'RM_10', 'name' => 'Candi', 'lat' => -7.4710, 'long' => 112.7210],
            ['kode' => 'RM_11', 'name' => 'Sidoarjo', 'lat' => -7.4410, 'long' => 112.7110],
            ['kode' => 'RM_12', 'name' => 'Buduran', 'lat' => -7.4110, 'long' => 112.7210],
            ['kode' => 'RM_13', 'name' => 'Gedangan', 'lat' => -7.3810, 'long' => 112.7310],
            ['kode' => 'RM_14', 'name' => 'Waru', 'lat' => -7.3510, 'long' => 112.7410],
            ['kode' => 'RM_15', 'name' => 'Wonocolo', 'lat' => -7.3210, 'long' => 112.7410],
            ['kode' => 'RM_16', 'name' => 'Putat Gedhe', 'lat' => -7.2710, 'long' => 112.6910],
            ['kode' => 'RM_17', 'name' => 'Alas Malang', 'lat' => -7.2810, 'long' => 112.6810],
            ['kode' => 'RM_18', 'name' => 'Giri', 'lat' => -7.1610, 'long' => 112.6210],
        ];

        foreach ($listRumahMeter as $rm) {
            Station::updateOrCreate(['kode_stasiun' => $rm['kode']], [
                'name' => $rm['name'],
                'type' => 'rumah_meter',
                'latitude' => $rm['lat'],
                'longitude' => $rm['long'],
                'radius_meters' => 300,
            ]);
        }

        // ==========================================
        // 4. MASTER ROLES
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();

        $rolesData = [
            // 1. TOP MANAGEMENT & DEPARTEMEN PUSAT (1 Tahap Approval, Bebas Stasiun)
            ['id' => 1,  'role_name' => 'Admin', 'level' => 1, 'description' => 'Administrator Utama Sistem ERP', 'parent_role_id' => 18, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => false]],
            ['id' => 2,  'role_name' => 'SECRETARY', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 3,  'role_name' => 'EXCECUTIVE ADVISOR', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 4,  'role_name' => 'PROCUREMENT', 'level' => 1, 'description' => null, 'parent_role_id' => null, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 5,  'role_name' => 'HRD', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 6,  'role_name' => 'CONSULTANT', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 7,  'role_name' => 'GENERAL MANAGER', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 8,  'role_name' => 'OPERATIONAL', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 9,  'role_name' => 'PUBLIC RELATIONS', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 10, 'role_name' => 'SUPORT', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 11, 'role_name' => 'LEGAL', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 12, 'role_name' => 'FINANCE', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => ['approval_rules' => 1, 'require_same_station' => false]],
            ['id' => 13, 'role_name' => 'GENERAL AFFAIRS', 'level' => 2, 'description' => null, 'parent_role_id' => 10, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 14, 'role_name' => 'ASSET', 'level' => 2, 'description' => null, 'parent_role_id' => 11, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 15, 'role_name' => 'ACCOUNT', 'level' => 2, 'description' => null, 'parent_role_id' => 12, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 16, 'role_name' => 'MARKETING', 'level' => 2, 'description' => null, 'parent_role_id' => 12, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 17, 'role_name' => 'DOKUMENT CONTROL', 'level' => 3, 'description' => null, 'parent_role_id' => 14, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 18, 'role_name' => 'Unit Booster-M', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],

            // 2. TIM LAPANGAN BOOSTER-M (2 Tahap Approval, Wajib Sama Stasiun)
            ['id' => 19, 'role_name' => 'Maintanance (Booster-M)', 'level' => 3, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
            ['id' => 20, 'role_name' => 'Q.HSE (Booster-M)', 'level' => 3, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
            ['id' => 21, 'role_name' => 'Operator (Booster-M)', 'level' => 3, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
            ['id' => 22, 'role_name' => 'AREA (PIPELINE)', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],
            ['id' => 23, 'role_name' => 'Unit IPA Umbulan (Instalasi Pengelolahan Air)', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => ['approval_levels' => 1, 'require_same_station' => false]],

            // 3. TIM LAPANGAN UMBULAN (2 Tahap Approval, Wajib Sama Stasiun)
            ['id' => 24, 'role_name' => 'Operator (Umbulan)', 'level' => 3, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
            ['id' => 25, 'role_name' => 'Maintanance (Umbulan)', 'level' => 3, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
            ['id' => 26, 'role_name' => 'Q.HSE (Umbulan)', 'level' => 3, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => ['approval_levels' => 2, 'require_same_station' => true]],
        ];

        foreach ($rolesData as $role) {
            Role::create([
                'id'             => $role['id'],
                'role_name'      => $role['role_name'],
                'level'          => $role['level'],
                'description'    => $role['description'],
                'parent_role_id' => $role['parent_role_id'],
                'approval_rules' => $role['approval_rules'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // PEMBICU PEMBENTUKAN TREE_CODE DAN LEVEL JABATAN
        $this->rebuildRoleTree();

        // ==========================================
        // 5. MASTER JENIS CUTI & SUB-CUTI
        // ==========================================
        $ijinIMP = JenisCuti::where('kode_cuti', 'IMPI')->first();
        $cutiTahunan = JenisCuti::where('kode_cuti', 'CT')->first();

        if (!$ijinIMP) {
            $ijinIMP = JenisCuti::create([
                'kode_cuti'          => 'IMPI',
                'name_cuti'          => 'Ijin Meninggalkan Pekerjaan',
                'kuota_default'      => null,
                'butuh_surat_dokter' => false,
                'keterangan'         => null,
            ]);
        }

        if (!$cutiTahunan) {
            $cutiTahunan = JenisCuti::create([
                'kode_cuti'          => 'CT',
                'name_cuti'          => 'Cuti',
                'kuota_default'      => 12,
                'butuh_surat_dokter' => false,
                'keterangan'         => null,
            ]);
        }

        // ==========================================
        // 6. SEEDING AKUN ADMIN UTAMA & KARYAWAN
        // ==========================================
        $defaultPassword = Hash::make('User123.');

        // ADMIN SISTEM
        $admin = User::create([
            'nip'               => 'ADMIN-001',
            'name'              => 'Admin Sistem',
            'email'             => 'admin@meta.com',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'gender_id'         => $pria->id,
            'station_id'        => $stBooster->id,
            'phone_number'      => '081234567890',
            'schedule_type'     => 'roster',
            'roster_start_date' => '2026-08-01',
            'normal_work_days'  => null,
            'normal_check_in'   => null,
            'normal_check_out'  => null,
            'password'          => Hash::make('Admin123.'),
        ]);

        // Attach 2 Role: ID 1 (Admin Utama) & ID 18 (Unit Booster-M)
        $admin->roles()->attach([
            1  => ['is_primary' => true],
        ]);

        SaldoCuti::create([
            'user_id'       => $admin->id,
            'jenis_cuti_id' => $cutiTahunan->id,
            'tahun'         => 2026,
            'sisa_saldo'    => 12,
        ]);

        // DAFTAR KARYAWAN (role_ids berbentuk array)
        $usersData = [
            // 1. TOP MANAGEMENT & DIREKSI
            ['nip' => 'EMP-002', 'name' => 'Herta Eridani', 'email' => 'herta@meta.com', 'role_ids' => [7], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // 2. SECRETARY, ADVISOR, CONSULTANT, PROCUREMENT, HRD
            ['nip' => 'EMP-003', 'name' => 'Nariessa S', 'email' => 'nariessa@meta.com', 'role_ids' => [2], 'station_id' => $stSurabaya->id, 'gender_id' => $wanita->id],
            ['nip' => 'EMP-004', 'name' => 'Yan Kuryana', 'email' => 'yan@meta.com', 'role_ids' => [3], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-005', 'name' => 'Rino Tumilar', 'email' => 'rino@meta.com', 'role_ids' => [3], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-006', 'name' => 'Hendra Tanzil', 'email' => 'hendra@meta.com', 'role_ids' => [6], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // CONTOH RANGKAP JABATAN: Pak Reki M. merangkap PROCUREMENT (4) & OPERATIONAL (8)
            ['nip' => 'EMP-007', 'name' => 'Reki M.', 'email' => 'reki@meta.com', 'role_ids' => [4, 8], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            ['nip' => 'EMP-008', 'name' => 'Bantolo E.', 'email' => 'bantolo@meta.com', 'role_ids' => [5], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-009', 'name' => 'Rifky', 'email' => 'rifky@meta.com', 'role_ids' => [5], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // 3. OPERATIONAL DIVISION HEADS
            ['nip' => 'EMP-010', 'name' => 'Jusman R.', 'email' => 'jusman@meta.com', 'role_ids' => [8], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // 4. DEPARTEMEN PENDUKUNG
            ['nip' => 'EMP-012', 'name' => 'Kurnia Suryandi', 'email' => 'kurnia@meta.com', 'role_ids' => [9], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-013', 'name' => 'Megantara Putera P.', 'email' => 'megantara@meta.com', 'role_ids' => [9], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-014', 'name' => 'Facthur R.', 'email' => 'facthur@meta.com', 'role_ids' => [10], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-015', 'name' => 'Moch. Anwar', 'email' => 'anwar@meta.com', 'role_ids' => [10], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-016', 'name' => 'Mufti A. P.', 'email' => 'mufti@meta.com', 'role_ids' => [10], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-017', 'name' => 'Hani A.', 'email' => 'hani@meta.com', 'role_ids' => [10], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-018', 'name' => 'Haidar T.', 'email' => 'haidar@meta.com', 'role_ids' => [11], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-019', 'name' => 'Adi P.', 'email' => 'adi@meta.com', 'role_ids' => [11], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-020', 'name' => 'Hanny Artika', 'email' => 'hanny@meta.com', 'role_ids' => [12], 'station_id' => $stSurabaya->id, 'gender_id' => $wanita->id],
            ['nip' => 'EMP-021', 'name' => 'Iqbal Hawari', 'email' => 'iqbal@meta.com', 'role_ids' => [12], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-022', 'name' => 'Derry', 'email' => 'derry@meta.com', 'role_ids' => [12], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // 5. SUB-DEPARTEMEN
            ['nip' => 'EMP-023', 'name' => 'Agung Dwi Nugroho', 'email' => 'agung@meta.com', 'role_ids' => [13], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-024', 'name' => 'Megantara P.', 'email' => 'megantara_asset@meta.com', 'role_ids' => [14], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-025', 'name' => 'Haidar Thalib', 'email' => 'haidar_asset@meta.com', 'role_ids' => [14], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-026', 'name' => 'Agung Dwi Nugroho B', 'email' => 'agungdwi@meta.com', 'role_ids' => [14], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-027', 'name' => 'Pontas Nipitulu', 'email' => 'pontas@meta.com', 'role_ids' => [15], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-028', 'name' => 'Erry K.', 'email' => 'erry@meta.com', 'role_ids' => [15], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-029', 'name' => 'Moch. Anwar B', 'email' => 'anwar_mkt@meta.com', 'role_ids' => [16], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-030', 'name' => 'Kurnia Suryandi B', 'email' => 'kurnia_mkt@meta.com', 'role_ids' => [16], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // 6. UNIT IPA UMBULAN & TIM LAPANGAN
            ['nip' => 'EMP-031', 'name' => 'Wahyu S.', 'email' => 'wahyus@meta.com', 'role_ids' => [23], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-032', 'name' => 'M. Iqbal', 'email' => 'iqbalm@meta.com', 'role_ids' => [23], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-033', 'name' => 'Duanda', 'email' => 'duanda@meta.com', 'role_ids' => [25], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-034', 'name' => 'Dwita Mido Gumelar', 'email' => 'dwita@meta.com', 'role_ids' => [25], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-035', 'name' => 'M. Ridwan', 'email' => 'ridwan@meta.com', 'role_ids' => [25], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-036', 'name' => 'Vivin S.', 'email' => 'vivin@meta.com', 'role_ids' => [26], 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
            ['nip' => 'EMP-037', 'name' => 'Eva Dina K.', 'email' => 'eva@meta.com', 'role_ids' => [26], 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
            ['nip' => 'EMP-038', 'name' => 'Triatmo Santoso', 'email' => 'triatmo@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-039', 'name' => 'Irwan Maulana', 'email' => 'irwan@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-040', 'name' => 'Safiul Anam', 'email' => 'saiful@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-041', 'name' => 'Hilman Maskuri', 'email' => 'hilman@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-042', 'name' => 'Allul Fikri', 'email' => 'allul@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-043', 'name' => 'Rasiono', 'email' => 'rasiono@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-044', 'name' => 'Safiudin', 'email' => 'safiudin@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-045', 'name' => 'M. Bahrul Ulum', 'email' => 'bahrul@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-046', 'name' => 'Lukman Taufiki', 'email' => 'lukman@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-047', 'name' => 'Wahyu Aditya', 'email' => 'wahyuaditya@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-048', 'name' => 'Muhamad Efendi', 'email' => 'efendi@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-049', 'name' => 'Ahmad Zainudin MZ', 'email' => 'zainudin@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-050', 'name' => 'Rifky Pratama', 'email' => 'rifkypratama@meta.com', 'role_ids' => [24], 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],

            // 7. AREA PIPELINE & UNIT BOOSTER-M
            ['nip' => 'EMP-051', 'name' => 'Mat Dawud', 'email' => 'dawud@meta.com', 'role_ids' => [22], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-052', 'name' => 'Ulin Nuha', 'email' => 'ulin@meta.com', 'role_ids' => [22], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-053', 'name' => 'Ikhwan', 'email' => 'ikhwan@meta.com', 'role_ids' => [22], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-054', 'name' => 'Oki', 'email' => 'oki@meta.com', 'role_ids' => [22], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-055', 'name' => 'Erwin C. H.', 'email' => 'erwin@meta.com', 'role_ids' => [18], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-056', 'name' => 'M Jafar', 'email' => 'jafar@meta.com', 'role_ids' => [19], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-057', 'name' => 'Ismi Syarifi', 'email' => 'ismi@meta.com', 'role_ids' => [20], 'station_id' => $stBooster->id, 'gender_id' => $wanita->id],
            ['nip' => 'EMP-058', 'name' => 'Khoirul Anam', 'email' => 'anam@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-059', 'name' => 'Yoga Farely', 'email' => 'yogafarely@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-060', 'name' => 'Misbahul Munir', 'email' => 'munir@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-061', 'name' => 'Ach Nafis', 'email' => 'nafis@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-062', 'name' => 'Udin', 'email' => 'udin@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-063', 'name' => 'Umar Khusni', 'email' => 'umar@meta.com', 'role_ids' => [21], 'station_id' => $stBooster->id, 'gender_id' => $pria->id],

            // 8. DOCUMENT CONTROL
            ['nip' => 'EMP-064', 'name' => 'Erry P.', 'email' => 'erry_doc@meta.com', 'role_ids' => [17], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['nip' => 'EMP-065', 'name' => 'Devi', 'email' => 'devi@meta.com', 'role_ids' => [17], 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
        ];

        foreach ($usersData as $userData) {
            $user = User::create([
                'nip'               => $userData['nip'],
                'name'              => $userData['name'],
                'email'             => $userData['email'],
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'gender_id'         => $userData['gender_id'],
                'station_id'        => $userData['station_id'],
                'phone_number'      => '0812' . rand(10000000, 99999999),
                'schedule_type'     => 'normal',
                'normal_work_days'  => '2026-08-18',
                'normal_check_in'   => '08:00',
                'normal_check_out'  => '17:00',
                'password'          => $defaultPassword,
            ]);

            // Simpan relasi ke tabel pivot role_user
            if (isset($userData['role_ids']) && is_array($userData['role_ids'])) {
                foreach ($userData['role_ids'] as $index => $roleId) {
                    $user->roles()->attach($roleId, [
                        'is_primary' => ($index === 0)
                    ]);
                }
            }

            SaldoCuti::create([
                'user_id'       => $user->id,
                'jenis_cuti_id' => $cutiTahunan->id,
                'tahun'         => 2026,
                'sisa_saldo'    => 12,
            ]);
        }
    }

    private function rebuildRoleTree()
    {
        $topRoles = Role::whereNull('parent_role_id')->orderBy('id', 'asc')->get();

        if ($topRoles->isEmpty()) {
            $topRoles = Role::where('id', Role::min('id'))->get();
        }

        $index = 1;
        foreach ($topRoles as $role) {
            $this->assignTreeCodeRecursively($role, (string) $index, 1);
            $index++;
        }
    }

    private function assignTreeCodeRecursively(Role $role, string $codePrefix, int $currentLevel)
    {
        $role->update([
            'tree_code' => $codePrefix,
        ]);

        $childRoles = Role::where('parent_role_id', $role->id)
            ->where('id', '!=', $role->id)
            ->orderBy('id', 'asc')
            ->get();

        $subIndex = 1;
        foreach ($childRoles as $child) {
            $newPrefix = $codePrefix . '.' . $subIndex;
            $this->assignTreeCodeRecursively($child, $newPrefix, $currentLevel + 1);
            $subIndex++;
        }
    }
}
