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

    /**
     * Seed the application's database.
     */
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

        // A. Stasiun & Kantor Utama
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

        // B. Rumah Meter / RM (18 Lokasi Service Murni)
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
        // 4. MASTER ROLES (SESUAI DUMP SQL)
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Role::truncate();

        $rulesDefault = json_encode([
            "approval_levels" => 1,
            "require_same_sektor" => false,
            "require_same_jobdesk" => false,
            "require_same_station" => true
        ]);

        $rulesLevel2 = json_encode([
            "approval_levels" => 2,
            "require_same_sektor" => false,
            "require_same_jobdesk" => false,
            "require_same_station" => true
        ]);

        $rolesData = [
            ['id' => 1, 'role_name' => 'Admin', 'level' => 1, 'description' => 'Administrator Utama Sistem ERP', 'parent_role_id' => 18, 'approval_rules' => $rulesDefault],
            ['id' => 2, 'role_name' => 'SECRETARY', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => $rulesDefault],
            ['id' => 3, 'role_name' => 'EXCECUTIVE ADVISOR', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => $rulesDefault],
            ['id' => 4, 'role_name' => 'PROCUREMENT', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => $rulesDefault],
            ['id' => 5, 'role_name' => 'HRD', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => $rulesDefault],
            ['id' => 6, 'role_name' => 'CONSULTANT', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 7, 'role_name' => 'GENERAL MANAGER', 'level' => 2, 'description' => null, 'parent_role_id' => null, 'approval_rules' => $rulesDefault],
            ['id' => 8, 'role_name' => 'OPERATIONAL', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 9, 'role_name' => 'PUBLIC RELATIONS', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 10, 'role_name' => 'SUPORT', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 11, 'role_name' => 'LEGAL', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 12, 'role_name' => 'FINANCE', 'level' => 2, 'description' => null, 'parent_role_id' => 7, 'approval_rules' => $rulesDefault],
            ['id' => 13, 'role_name' => 'GENERAL AFFAIRS', 'level' => 2, 'description' => null, 'parent_role_id' => 10, 'approval_rules' => $rulesDefault],
            ['id' => 14, 'role_name' => 'ASSET', 'level' => 2, 'description' => null, 'parent_role_id' => 11, 'approval_rules' => $rulesDefault],
            ['id' => 15, 'role_name' => 'ACCOUNT', 'level' => 2, 'description' => null, 'parent_role_id' => 12, 'approval_rules' => $rulesDefault],
            ['id' => 16, 'role_name' => 'MARKETING', 'level' => 2, 'description' => null, 'parent_role_id' => 12, 'approval_rules' => $rulesDefault],
            ['id' => 17, 'role_name' => 'DOKUMENT CONTROL', 'level' => 2, 'description' => null, 'parent_role_id' => 14, 'approval_rules' => $rulesDefault],
            ['id' => 18, 'role_name' => 'Unit Booster-M', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => $rulesDefault],
            ['id' => 19, 'role_name' => 'Maintanance (Booster-M)', 'level' => 2, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => $rulesLevel2],
            ['id' => 20, 'role_name' => 'Q.HSE (Booster-M)', 'level' => 2, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => $rulesLevel2],
            ['id' => 21, 'role_name' => 'Operator (Booster-M)', 'level' => 2, 'description' => null, 'parent_role_id' => 18, 'approval_rules' => $rulesLevel2],
            ['id' => 22, 'role_name' => 'AREA (PIPELINE)', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => $rulesDefault],
            ['id' => 23, 'role_name' => 'Unit IPA Umbulan (Instalasi Pengelolahan Air)', 'level' => 2, 'description' => null, 'parent_role_id' => 8, 'approval_rules' => $rulesDefault],
            ['id' => 24, 'role_name' => 'Operator (Umbulan)', 'level' => 2, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => $rulesLevel2],
            ['id' => 25, 'role_name' => 'Maintanance (Umbulan)', 'level' => 2, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => $rulesLevel2],
            ['id' => 26, 'role_name' => 'Q.HSE (Umbulan)', 'level' => 2, 'description' => null, 'parent_role_id' => 23, 'approval_rules' => $rulesLevel2],
        ];

        foreach ($rolesData as $role) {
            Role::create([
                'id' => $role['id'],
                'role_name' => $role['role_name'],
                'level' => $role['level'],
                'description' => $role['description'],
                'parent_role_id' => $role['parent_role_id'],
                'approval_rules' => json_decode($role['approval_rules'], true),
                'created_at' => now(),
                'updated_at' => now(),
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
        // 6. SEEDING AKUN ADMIN UTAMA
        // ==========================================
        $adminRole = Role::find(1);

        $admin = User::create([
            'nip'               => 'ADMIN-001',
            'name'              => 'Admin Sistem',
            'email'             => 'admin@meta.com',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'role_id'           => $adminRole->id,
            'gender_id'         => $pria->id,
            'station_id'        => $stBooster->id,
            'phone_number'      => '081234567890',
            'schedule_type'     => 'normal',
            'normal_work_days'  => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
            'normal_check_in'   => '00:00',
            'normal_check_out'  => '00:00',
            'password'          => Hash::make('Admin123.'),
        ]);

        // Saldo Cuti untuk Admin
        SaldoCuti::create([
            'user_id'       => $admin->id,
            'jenis_cuti_id' => $cutiTahunan->id,
            'tahun'         => 2026,
            'sisa_saldo'    => 12,
        ]);
    }
}
