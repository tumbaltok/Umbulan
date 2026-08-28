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
        Storage::disk('public')->deleteDirectory('dokumen_car');
        Storage::disk('public')->deleteDirectory('dokumen_mpr');

        Storage::disk('public')->makeDirectory('profile_photos');
        Storage::disk('public')->makeDirectory('dokumen_cuti');
        Storage::disk('public')->makeDirectory('dokumen_car');
        Storage::disk('public')->makeDirectory('dokumen_mpr');

        // ==========================================
        // 2. MASTER GENDERS
        // ==========================================
        $pria = Gender::firstOrCreate(['name' => 'Pria']);
        $wanita = Gender::firstOrCreate(['name' => 'Wanita']);

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
        // 4. MASTER ROLES (DENGAN PARENT_ROLE_ID)
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();

        $rolesData = [
            // Direksi & Head Division
            ['id' => 2,  'role_name' => 'EXCECUTIVE ADVISOR', 'parent_role_id' => null, 'description' => null],
            ['id' => 3,  'role_name' => 'PROCUREMENT', 'parent_role_id' => null, 'description' => null],
            ['id' => 4,  'role_name' => 'GENERAL MANAGER', 'parent_role_id' => null, 'description' => null],
            ['id' => 5,  'role_name' => 'SECRETARY', 'parent_role_id' => null, 'description' => null],
            ['id' => 6,  'role_name' => 'HRD', 'parent_role_id' => null, 'description' => null],

            // Berada langsung di bawah GM - Role 7
            ['id' => 7,  'role_name' => 'CONSULTANT', 'parent_role_id' => 4, 'description' => null],
            ['id' => 8,  'role_name' => 'OPERATIONAL', 'parent_role_id' => 4, 'description' => null],
            ['id' => 9,  'role_name' => 'PUBLIC RELATIONS', 'parent_role_id' => 4, 'description' => null],
            ['id' => 10, 'role_name' => 'SUPORT', 'parent_role_id' => 4, 'description' => null],
            ['id' => 11, 'role_name' => 'LEGAL', 'parent_role_id' => 4, 'description' => null],
            ['id' => 12, 'role_name' => 'FINANCE', 'parent_role_id' => 4, 'description' => null],

            // Operational Sub-Units (Bawahan Operational - Role 8)
            ['id' => 13, 'role_name' => 'UNIT IPA UMBULAN', 'parent_role_id' => 8, 'description' => null],
            ['id' => 14, 'role_name' => 'AREA (PIPELINE)', 'parent_role_id' => 8, 'description' => null],
            ['id' => 15, 'role_name' => 'UNIT BOOSTER-M', 'parent_role_id' => 8, 'description' => null],

            // Sub-Departemen
            ['id' => 16, 'role_name' => 'GENERAL AFFAIRS', 'parent_role_id' => 10, 'description' => null],
            ['id' => 17, 'role_name' => 'ASSET', 'parent_role_id' => 11, 'description' => null],
            ['id' => 18, 'role_name' => 'ACCOUNT', 'parent_role_id' => 12, 'description' => null],
            ['id' => 19, 'role_name' => 'MARKETING', 'parent_role_id' => 12, 'description' => null],
            ['id' => 27, 'role_name' => 'DOKUMENT CONTROL', 'parent_role_id' => 17, 'description' => null],

            // Tim Lapangan (Bawahan Unit IPA Umbulan - Role 13)
            ['id' => 20, 'role_name' => 'MAINTANANCE (Umbulan)', 'parent_role_id' => 13, 'description' => null],
            ['id' => 21, 'role_name' => 'Q.HSE (Umbulan)', 'parent_role_id' => 13, 'description' => null],
            ['id' => 22, 'role_name' => 'GENERAL SERVICES', 'parent_role_id' => 13, 'description' => null],
            ['id' => 23, 'role_name' => 'OPERATOR (Umbulan)', 'parent_role_id' => 13, 'description' => null],

            // Tim Lapangan (Bawahan Unit Booster-M - Role 15)
            ['id' => 24, 'role_name' => 'MAINTANANCE (Booster-M)', 'parent_role_id' => 15, 'description' => null],
            ['id' => 25, 'role_name' => 'Q.HSE (Booster-M)', 'parent_role_id' => 15, 'description' => null],
            ['id' => 26, 'role_name' => 'OPERATOR (Booster-M)', 'parent_role_id' => 15, 'description' => null],
            ];

        foreach ($rolesData as $role) {
            Role::create([
                'id'             => $role['id'],
                'role_name'      => $role['role_name'],
                'parent_role_id' => $role['parent_role_id'],
                'approval_rules' => $role['approval_rules'] ?? null,
                'description'    => $role['description'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
        // 6. SEEDING AKUN KARYAWAN & ADMINISTRATOR
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        User::truncate();
        DB::table('role_user')->truncate();
        DB::table('station_user')->truncate();
        SaldoCuti::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $defaultPassword = Hash::make('User123.');

        // ------------------------------------------------------------------
        // PENGELOMPOKAN NIP/KARYAWAN BERDASARKAN JADWAL
        // ------------------------------------------------------------------

        // Kelompok Roster Gelombang 1 (Mulai 04-08-2020)
        $rosterGroupA = ['EMP-037', 'EMP-042', 'EMP-043', 'EMP-044', 'EMP-045', 'EMP-056', 'EMP-057'];

        // Kelompok Roster Gelombang 2 (Mulai 11-08-2020)
        $rosterGroupB = ['EMP-029', 'EMP-036', 'EMP-050', 'EMP-051', 'EMP-052', 'EMP-053', 'EMP-059', 'EMP-060'];

        // Kelompok Roster Gelombang 3 (Mulai 18-08-2020)
        $rosterGroupC = ['EMP-030', 'EMP-039', 'EMP-046', 'EMP-047', 'EMP-048', 'EMP-049', 'EMP-058', 'EMP-061'];

        // DAFTAR KARYAWAN (Dengan Dukungan Multi-Role / Rangkap Jabatan)
        $usersData = [
            // TOP MANAGEMENT
            ['id' => 1, 'nip' => 'EMP-003', 'name' => 'Yan Kuryana', 'email' => 'yan@meta.com', 'role_id' => 2, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 2, 'nip' => 'EMP-004', 'name' => 'Rino Tumilar', 'email' => 'rino@meta.com', 'role_id' => 2, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            // Multi-Role: Reki M (PROCUREMENT & OPERATIONAL - Level 1)
            ['id' => 3, 'nip' => 'EMP-006', 'name' => 'Reki M', 'email' => 'reki@meta.com', 'role_id' => 3, 'roles' => [3, 8], 'level' => 1, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 4, 'nip' => 'EMP-001', 'name' => 'Herta Eridani', 'email' => 'herta@meta.com', 'role_id' => 4, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 5, 'nip' => 'EMP-002', 'name' => 'Nariessa S', 'email' => 'nariessa@meta.com', 'role_id' => 5, 'station_id' => $stSurabaya->id, 'gender_id' => $wanita->id],
            ['id' => 6, 'nip' => 'EMP-007', 'name' => 'Bantolo E.', 'email' => 'bantolo@meta.com', 'role_id' => 5, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 7, 'nip' => 'EMP-008', 'name' => 'Rifky', 'email' => 'rifky@meta.com', 'role_id' => 5, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // HEAD DIVISION / UNDER GENERAL MANAGER
            ['id' => 8, 'nip' => 'EMP-005', 'name' => 'Hendra Tanzil', 'email' => 'hendra@meta.com', 'role_id' => 6, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 9, 'nip' => 'EMP-009', 'name' => 'Jusman R.', 'email' => 'jusman@meta.com', 'role_id' => 8, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            // Multi-Role: Kurnia Suryandi (PUBLIC RELATIONS & MARKETING)
            ['id' => 10, 'nip' => 'EMP-010', 'name' => 'Kurnia Suryandi', 'email' => 'kurnia@meta.com', 'role_id' => 9, 'roles' => [9, 19], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            // Multi-Role: Megantara Putera P. (PUBLIC RELATIONS & ASSET)
            ['id' => 11, 'nip' => 'EMP-011', 'name' => 'Megantara Putera P.', 'email' => 'megantara@meta.com', 'role_id' => 9, 'roles' => [9, 17], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 12, 'nip' => 'EMP-012', 'name' => 'Facthur R.', 'email' => 'facthur@meta.com', 'role_id' => 10, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            // Multi-Role: Moch. Anwar (SUPORT & MARKETING)
            ['id' => 13, 'nip' => 'EMP-013', 'name' => 'Moch. Anwar', 'email' => 'anwar@meta.com', 'role_id' => 10, 'roles' => [10, 19], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 14, 'nip' => 'EMP-014', 'name' => 'Mufti A. P.', 'email' => 'mufti@meta.com', 'role_id' => 10, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 15, 'nip' => 'EMP-015', 'name' => 'Hani A.', 'email' => 'hani@meta.com', 'role_id' => 10, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            // Multi-Role: Haidar Thalib (LEGAL & ASSET)
            ['id' => 16, 'nip' => 'EMP-016', 'name' => 'Haidar Thalib', 'email' => 'haidar@meta.com', 'role_id' => 11, 'roles' => [11, 17], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 17, 'nip' => 'EMP-017', 'name' => 'Adi P.', 'email' => 'adi@meta.com', 'role_id' => 11, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 18, 'nip' => 'EMP-018', 'name' => 'Hanny Artika', 'email' => 'hanny@meta.com', 'role_id' => 12, 'station_id' => $stSurabaya->id, 'gender_id' => $wanita->id],
            ['id' => 19, 'nip' => 'EMP-019', 'name' => 'Iqbal Hawari', 'email' => 'iqbal@meta.com', 'role_id' => 12, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 20, 'nip' => 'EMP-020', 'name' => 'Derry', 'email' => 'derry@meta.com', 'role_id' => 12, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // OPERATIONAL BRANCHES
            ['id' => 29, 'nip' => 'EMP-029', 'name' => 'Wahyu S.', 'email' => 'wahyus@meta.com', 'role_id' => 13, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 30, 'nip' => 'EMP-030', 'name' => 'M. Iqbal', 'email' => 'iqbalm@meta.com', 'role_id' => 13, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 31, 'nip' => 'EMP-031', 'name' => 'Mat Dawud', 'email' => 'dawud@meta.com', 'role_id' => 14, 'station_id' => $stBooster->id, 'gender_id' => $pria->id, 'assigned_rm' => ['RM_01', 'RM_02', 'RM_03', 'RM_04', 'RM_05', 'RM_06', 'RM_07', 'RM_08', 'RM_09']],
            ['id' => 32, 'nip' => 'EMP-032', 'name' => 'Ulin Nuha', 'email' => 'ulin@meta.com', 'role_id' => 14, 'station_id' => $stBooster->id, 'gender_id' => $pria->id, 'assigned_rm' => ['RM_10', 'RM_11', 'RM_12', 'RM_13', 'RM_14', 'RM_15', 'RM_16', 'RM_17', 'RM_18']],
            ['id' => 33, 'nip' => 'EMP-033', 'name' => 'Ikhwan', 'email' => 'ikhwan@meta.com', 'role_id' => 14, 'station_id' => $stBooster->id, 'gender_id' => $pria->id, 'assigned_rm' => ['RM_01', 'RM_02', 'RM_03', 'RM_04', 'RM_05', 'RM_06', 'RM_07', 'RM_08', 'RM_09']],
            ['id' => 34, 'nip' => 'EMP-034', 'name' => 'Okik', 'email' => 'okik@meta.com', 'role_id' => 14, 'station_id' => $stBooster->id, 'gender_id' => $pria->id, 'assigned_rm' => ['RM_10', 'RM_11', 'RM_12', 'RM_13', 'RM_14', 'RM_15', 'RM_16', 'RM_17', 'RM_18']],
            ['id' => 35, 'nip' => 'EMP-035', 'name' => 'Erwin C. H.', 'email' => 'erwin@meta.com', 'role_id' => 15, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],

            // SUB-DEPARTEMEN
            ['id' => 21, 'nip' => 'EMP-021', 'name' => 'Agung Dwi Nugroho', 'email' => 'agung@meta.com', 'role_id' => 16, 'roles' => [16, 17], 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 25, 'nip' => 'EMP-025', 'name' => 'Pontas Nipitulu', 'email' => 'pontas@meta.com', 'role_id' => 18, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 26, 'nip' => 'EMP-026', 'name' => 'Erry K.', 'email' => 'erry@meta.com', 'role_id' => 18, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],

            // TIM LAPANGAN UMBULAN
            ['id' => 36, 'nip' => 'EMP-036', 'name' => 'Duanda', 'email' => 'duanda@meta.com', 'role_id' => 20, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 37, 'nip' => 'EMP-037', 'name' => 'Dwita Mido Gumelar', 'email' => 'dwita@meta.com', 'role_id' => 20, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 38, 'nip' => 'EMP-038', 'name' => 'M. Ridwan', 'email' => 'ridwan@meta.com', 'role_id' => 20, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 39, 'nip' => 'EMP-039', 'name' => 'Vivin S.', 'email' => 'vivin@meta.com', 'role_id' => 21, 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
            ['id' => 40, 'nip' => 'EMP-040', 'name' => 'Eva Dina K.', 'email' => 'eva@meta.com', 'role_id' => 21, 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
            ['id' => 41, 'nip' => 'EMP-041', 'name' => 'Triatmo Santoso', 'email' => 'triatmo@meta.com', 'role_id' => 22, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 42, 'nip' => 'EMP-042', 'name' => 'Irwan Maulana', 'email' => 'irwan@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 43, 'nip' => 'EMP-043', 'name' => 'Safiul Anam', 'email' => 'saiful@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 44, 'nip' => 'EMP-044', 'name' => 'Hilman Maskuri', 'email' => 'hilman@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 45, 'nip' => 'EMP-045', 'name' => 'Allul Fikri', 'email' => 'allul@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 46, 'nip' => 'EMP-046', 'name' => 'Rasiono', 'email' => 'rasiono@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 47, 'nip' => 'EMP-047', 'name' => 'Safiudin', 'email' => 'safiudin@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 48, 'nip' => 'EMP-048', 'name' => 'M. Bahrul Ulum', 'email' => 'bahrul@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 49, 'nip' => 'EMP-049', 'name' => 'Lukman Taufiki', 'email' => 'lukman@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 50, 'nip' => 'EMP-050', 'name' => 'Wahyu Aditya', 'email' => 'wahyuaditya@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 51, 'nip' => 'EMP-051', 'name' => 'Muhamad Efendi', 'email' => 'efendi@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 52, 'nip' => 'EMP-052', 'name' => 'Ahmad Zainudin MZ', 'email' => 'zainudin@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],
            ['id' => 53, 'nip' => 'EMP-053', 'name' => 'Rifky Pratama', 'email' => 'rifkypratama@meta.com', 'role_id' => 23, 'station_id' => $stUmbulan->id, 'gender_id' => $pria->id],

            // TIM LAPANGAN BOOSTER-M
            ['id' => 54, 'nip' => 'EMP-054', 'name' => 'M Jafar', 'email' => 'jafar@meta.com', 'role_id' => 24, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['id' => 55, 'nip' => 'EMP-055', 'name' => 'Ismi Syarifi', 'email' => 'ismi@meta.com', 'role_id' => 25, 'station_id' => $stBooster->id, 'gender_id' => $wanita->id],
            ['id' => 56, 'nip' => 'EMP-056', 'name' => 'Khoirul Anam', 'email' => 'anam@meta.com', 'role_id' => 26, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            // Yoga Farely (OPERATOR Booster-M - Level 1)
            ['id' => 57, 'nip' => 'EMP-057', 'name' => 'Yoga Farely', 'email' => 'yogafarely@meta.com', 'role_id' => 26, 'level' => 1, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['id' => 58, 'nip' => 'EMP-058', 'name' => 'Misbahul Munir', 'email' => 'munir@meta.com', 'role_id' => 26, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['id' => 59, 'nip' => 'EMP-059', 'name' => 'Ach Nafis', 'email' => 'nafis@meta.com', 'role_id' => 26, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['id' => 60, 'nip' => 'EMP-060', 'name' => 'Safiudin', 'email' => 'udin@meta.com', 'role_id' => 26, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],
            ['id' => 61, 'nip' => 'EMP-061', 'name' => 'Umar Khusni', 'email' => 'umar@meta.com', 'role_id' => 26, 'station_id' => $stBooster->id, 'gender_id' => $pria->id],

            // DOCUMENT CONTROL
            ['id' => 62, 'nip' => 'EMP-062', 'name' => 'Erry P.', 'email' => 'erry_doc@meta.com', 'role_id' => 27, 'station_id' => $stSurabaya->id, 'gender_id' => $pria->id],
            ['id' => 63, 'nip' => 'EMP-063', 'name' => 'Devi', 'email' => 'devi@meta.com', 'role_id' => 27, 'station_id' => $stUmbulan->id, 'gender_id' => $wanita->id],
        ];

        foreach ($usersData as $userData) {
            $nip = $userData['nip'];

            // Logika Evaluasi Jadwal Berdasarkan NIP
            if (in_array($nip, $rosterGroupA)) {
                $scheduleType    = 'roster';
                $rosterStartDate = '2026-08-01';
                $normalWorkDays  = null;
                $normalCheckIn   = null;
                $normalCheckOut  = null;
            } elseif (in_array($nip, $rosterGroupB)) {
                $scheduleType    = 'roster';
                $rosterStartDate = '2026-08-01';
                $normalWorkDays  = null;
                $normalCheckIn   = null;
                $normalCheckOut  = null;
            } elseif (in_array($nip, $rosterGroupC)) {
                $scheduleType    = 'roster';
                $rosterStartDate = '2026-08-01';
                $normalWorkDays  = null;
                $normalCheckIn   = null;
                $normalCheckOut  = null;
            } else {
                $scheduleType    = 'normal';
                $rosterStartDate = null;
                $normalWorkDays  = '1,2,3,4,5';
                $normalCheckIn   = '07:00:00';
                $normalCheckOut  = '16:00:00';
            }

            // Penentuan Hak Akses Akun: Reki M & Yoga Farely = Level 1 (Full Access), Seluruh Karyawan Lainnya = Level 2 (Monitoring)
            $isLevel1 = in_array($userData['name'], ['Reki M', 'Yoga Farely']) || ($userData['level'] ?? null) === 1;
            $userLevel = $isLevel1 ? 1 : 2;

            $user = User::create([
                'nip'               => $userData['nip'],
                'name'              => $userData['name'],
                'email'             => $userData['email'],
                'role_id'           => $userData['role_id'],
                'level'             => $userLevel,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'gender_id'         => $userData['gender_id'],
                'station_id'        => $userData['station_id'],
                'phone_number'      => '0812' . rand(10000000, 99999999),
                'signature'         => 'signatures/dummy_signature.png',
                'schedule_type'     => $scheduleType,
                'roster_start_date' => $rosterStartDate,
                'normal_work_days'  => $normalWorkDays,
                'normal_check_in'   => $normalCheckIn,
                'normal_check_out'  => $normalCheckOut,
                'password'          => $defaultPassword,
            ]);

            $rolesToSync = $userData['roles'] ?? [$userData['role_id']];
            $syncData = [];
            foreach ($rolesToSync as $idx => $rId) {
                $syncData[$rId] = ['is_primary' => ($idx === 0)];
            }
            $user->roles()->sync($syncData);

            if (!empty($userData['assigned_rm'])) {
                $rmStationIds = Station::whereIn('kode_stasiun', $userData['assigned_rm'])->pluck('id')->toArray();
                $user->assignedStations()->sync($rmStationIds);
            }

            SaldoCuti::create([
                'user_id'       => $user->id,
                'jenis_cuti_id' => $cutiTahunan->id,
                'tahun'         => (int) date('Y'),
                'sisa_saldo'    => 12,
            ]);
        }
    }
}
