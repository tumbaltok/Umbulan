<?php

use App\Models\User\Gender;
use App\Models\User\Role;
use App\Models\User\Station;
use App\Models\User\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menginjeksi data master sistem yang bersifat WAJIB (Genders, Stations, Roles, dan 1 Akun Super Admin),
     * sehingga aplikasi langsung siap pakai (production-ready) hanya dengan `php artisan migrate`.
     */
    public function up(): void
    {
        // 1. PASTIKAN FOLDER PENYIMPANAN TERSEDIA
        Storage::disk('public')->makeDirectory('profile_photos');
        Storage::disk('public')->makeDirectory('dokumen_cuti');
        Storage::disk('public')->makeDirectory('dokumen_car');
        Storage::disk('public')->makeDirectory('dokumen_mpr');

        // 2. MASTER GENDERS (Pria & Wanita)
        $pria = Gender::firstOrCreate(['name' => 'Pria']);
        $wanita = Gender::firstOrCreate(['name' => 'Wanita']);

        // 3. MASTER STATIONS (4 UTAMA + 18 RUMAH METER)
        $stUmbulan = Station::updateOrCreate(['kode_stasiun' => 'UMBULAN'], [
            'name' => 'Stasiun Umbulan',
            'type' => 'stasiun',
            'latitude' => -7.7572565,
            'longitude' => 112.9314949,
            'radius_meters' => 1000,
        ]);

        $stBooster = Station::updateOrCreate(['kode_stasiun' => 'BOOSTER_M'], [
            'name' => 'Stasiun Booster-M',
            'type' => 'stasiun',
            'latitude' => -7.5812341,
            'longitude' => 112.7212341,
            'radius_meters' => 500,
        ]);

        $stSurabaya = Station::updateOrCreate(['kode_stasiun' => 'HO_SBY'], [
            'name' => 'Kantor Surabaya',
            'type' => 'kantor',
            'latitude' => -7.2574719,
            'longitude' => 112.7520883,
            'radius_meters' => 200,
        ]);

        $stJakarta = Station::updateOrCreate(['kode_stasiun' => 'HO_JKT'], [
            'name' => 'Kantor Jakarta',
            'type' => 'kantor',
            'latitude' => -6.2087634,
            'longitude' => 106.8455990,
            'radius_meters' => 200,
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

        // 4. MASTER ROLES & STRUKTUR HIERARKI
        // Role Super Admin Tertinggi
        $adminRole = Role::firstOrCreate(
            ['role_name' => 'SUPER ADMIN'],
            ['description' => 'Administrator Tertinggi Sistem ERP']
        );

        // Daftar Role Utama
        $rawRoles = [
            'EXCECUTIVE ADVISOR'      => ['parent' => null],
            'PROCUREMENT'             => ['parent' => null],
            'GENERAL MANAGER'         => ['parent' => null],
            'SECRETARY'               => ['parent' => null],
            'HRD'                     => ['parent' => null],
            'CONSULTANT'              => ['parent' => 'GENERAL MANAGER'],
            'OPERATIONAL'             => ['parent' => 'GENERAL MANAGER'],
            'PUBLIC RELATIONS'        => ['parent' => 'GENERAL MANAGER'],
            'SUPORT'                  => ['parent' => 'GENERAL MANAGER'],
            'LEGAL'                   => ['parent' => 'GENERAL MANAGER'],
            'FINANCE'                 => ['parent' => 'GENERAL MANAGER'],
            'SPV OPERATOR (Umbulan)'  => ['parent' => 'OPERATIONAL'],
            'AREA (PIPELINE)'         => ['parent' => 'OPERATIONAL'],
            'SPV OPERATOR (Booster-M)'=> ['parent' => 'OPERATIONAL'],
            'GENERAL AFFAIRS'         => ['parent' => 'SUPORT'],
            'ASSET'                   => ['parent' => 'LEGAL'],
            'ACCOUNT'                 => ['parent' => 'FINANCE'],
            'MARKETING'               => ['parent' => 'FINANCE'],
            'DOKUMENT CONTROL'        => ['parent' => 'ASSET'],
            'OPERATOR (Umbulan)'      => ['parent' => 'SPV OPERATOR (Umbulan)'],
            'MAINTANANCE (Umbulan)'   => ['parent' => 'SPV OPERATOR (Umbulan)'],
            'Q.HSE (Umbulan)'         => ['parent' => 'SPV OPERATOR (Umbulan)'],
            'GENERAL SERVICES'        => ['parent' => 'SPV OPERATOR (Umbulan)'],
            'OPERATOR (Booster-M)'    => ['parent' => 'SPV OPERATOR (Booster-M)'],
            'MAINTANANCE (Booster-M)' => ['parent' => 'SPV OPERATOR (Booster-M)'],
            'Q.HSE (Booster-M)'       => ['parent' => 'SPV OPERATOR (Booster-M)'],
        ];

        // Buat atau temukan seluruh role terlebih dahulu
        $createdRoles = [];
        foreach ($rawRoles as $name => $meta) {
            $createdRoles[$name] = Role::firstOrCreate(['role_name' => $name]);
        }

        // Hubungkan parent_role_id sesuai relasi atasan
        foreach ($rawRoles as $name => $meta) {
            if ($meta['parent'] && isset($createdRoles[$meta['parent']])) {
                $createdRoles[$name]->update(['parent_role_id' => $createdRoles[$meta['parent']]->id]);
            }
        }

        // 5. SATU AKUN SUPER ADMINISTRATOR SISTEM (LEVEL 1)
        // Dibuat khusus untuk pengelola sistem, terpisah dari akun pengguna riil karyawan.
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@meta.com'],
            [
                'nip'               => 'ADM-001',
                'name'              => 'Super Administrator',
                'role_id'           => $adminRole->id,
                'level'             => 1, // Admin Level 1 (Akses Penuh Panel Admin)
                'gender_id'         => $pria->id,
                'station_id'        => $stSurabaya->id,
                'schedule_type'     => 'reguler_5_hari',
                'normal_work_days'  => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'normal_check_in'   => '07:00:00',
                'normal_check_out'  => '16:00:00',
                'password'          => Hash::make('Admin123.'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'phone_number'      => '081200000001',
                'signature'         => 'signatures/dummy_signature.png',
            ]
        );

        // Pastikan role_user pivot dan level tersinkronisasi untuk Super Admin
        if ($superAdmin) {
            $superAdmin->roles()->syncWithoutDetaching([$adminRole->id => ['is_primary' => true]]);
            $superAdmin->update(['level' => 1]);
        }

        // 6. KEMBALIKAN AKUN USER RIIL (REKI M & YOGA FARELY) KE LEVEL PENGGUNA ASLI JIKA ADA
        // Agar akun personil riil tidak terikat sebagai super admin sistem bawaan.
        User::whereIn('email', ['reki@meta.com', 'yogafarely@meta.com'])
            ->where('email', '!=', 'admin@meta.com')
            ->update(['level' => 3]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'admin@meta.com')->delete();
    }
};
