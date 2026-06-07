<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Tipe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\JenisCuti;
use App\Models\Station;
use Illuminate\Support\Facades\hash;
use App\Models\SaldoCuti;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // DATA MASTER ROLES
        $roleAdmin = Role::create(['role_name' => 'Admin']);
        $roleManager = Role::create(['role_name' => 'Manager']);
        $roleSpv = Role::create(['role_name' => 'Supervisor']);
        $roleKaryawan = Role::create(['role_name' => 'Karyawan']);

        // DATA JOBS
        $operator = Tipe::create([
            'kode_pekerjaan' => 'OP',
            'name' => 'Operator'
        ]);
        $maintanance = Tipe::create([
            'kode_pekerjaan' => 'MN',
            'name' => 'Maintanance'
        ]);
        $safety = Tipe::create([
            'kode_pekerjaan' => 'HSE',
            'name' => 'Safety'
        ]);
        $dokumen = Tipe::create([
            'kode_pekerjaan' => 'Docs',
            'name' => 'Dokumentasi'
        ]);

        // DATA MASTER GENDERS
        $pria = Gender::create(['name' => 'Pria']);
        $wanita = Gender::create(['name' => 'Wanita']);

        // DATA MASTER STATIONS
        $stasiunUmbulan = Station::create([
            'kode_stasiun' => 'umbulan',
            'name' => 'Stasiun Umbulan'
        ]);

        $stasiunBooster = Station::create([
            'kode_stasiun' => 'booster',
            'name' => 'Stasiun Booster-M'
        ]);

        // DATA MASTER JENIS CUTI
        $cutiTahunan = JenisCuti::create([
            'name_cuti' => 'Cuti Tahunan',
            'kuota_default' => 12,
            'butuh_surat_dokter' => false
        ]);

        $cutiSakit = JenisCuti::create([
            'name_cuti' => 'sakit',
            // 'kuota_default' => null,
            'butuh_surat_dokter' => true
        ]);

        $cutiMelahirkan = JenisCuti::create([
            'name_cuti' => 'melahirkan',
            // 'kuota_default' => null,
            'butuh_surat_dokter' => true
        ]);

        // ==========================================
        // DATA USERS (Karyawan & Atasan)
        // ==========================================

        // Akun Manager (Bebas Stasiun)
        $manager = User::create([
            'nip' => '100',
            'name' => 'Manager',
            'email' => 'manager@meta.com',
            'role_id' => $roleManager->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'password' => Hash::make('password123'),
        ]);

        // --- STASIUN UMBULAN ---
        $spvUmbulan = User::create([
            'nip' => '110',
            'name' => 'SPV_OPRATOR_UMBULAN (SPV Operator Umbulan)',
            'email' => 'spv.operator.umbulan@meta.com',
            'role_id' => $roleSpv->id,
            'tipe_id' => $operator->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'manager_id' => $manager->id, // Lapor ke Manager
            'password' => Hash::make('password123'),
        ]);

        $spvUmbulan = User::create([
            'nip' => '111',
            'name' => 'SPV_MAINTANACE_UMBULAN (SPV Maintanance Umbulan)',
            'email' => 'spv.maintanance.umbulan@meta.com',
            'role_id' => $roleSpv->id,
            'tipe_id' => $maintanance->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'manager_id' => $manager->id, // Lapor ke Manager
            'password' => Hash::make('password123'),
        ]);

        $spvUmbulan = User::create([
            'nip' => '112',
            'name' => 'SPV_HSE_UMBULAN (SPV HSE Umbulan)',
            'email' => 'spv.hse.umbulan@meta.com',
            'role_id' => $roleSpv->id,
            'tipe_id' => $safety->id,
            'gender_id' => $wanita->id,
            'station_id' => $stasiunUmbulan->id,
            'manager_id' => $manager->id, // Lapor ke Manager
            'password' => Hash::make('password123'),
        ]);

        $karyawanUmbulan = User::create([
            'nip' => '120',
            'name' => 'OPERATOR_UMBULAN',
            'email' => 'operator.umbulan@meta.com',
            'role_id' => $roleKaryawan->id,
            'tipe_id' => $operator->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'supervisor_id' => $spvUmbulan->id, //Lapor ke SPV-nya adalah SPV Umbulan
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        $karyawanUmbulan = User::create([
            'nip' => '121',
            'name' => 'MAINTANANCE_UMBULAN (Karyawan Umbulan)',
            'email' => 'maintanance.umbulan@meta.com',
            'role_id' => $maintanance->id,
            'tipe_id' => $maintanance->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'supervisor_id' => $spvUmbulan->id, //Lapor ke SPV-nya adalah SPV Umbulan
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        $karyawanUmbulan = User::create([
            'nip' => '122',
            'name' => 'HSE_umbulan',
            'email' => 'hse.umbulan@meta.com',
            'role_id' => $roleKaryawan->id,
            'tipe_id' => $safety->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'supervisor_id' => $spvUmbulan->id, //Lapor ke SPV-nya adalah SPV Umbulan
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        $karyawanUmbulan = User::create([
            'nip' => '123',
            'name' => 'DOCS_UMBULAN',
            'email' => 'docs.umbulan@meta.com',
            'role_id' => $dokumen->id,
            'tipe_id' => $dokumen->id,
            'gender_id' => $wanita->id,
            'station_id' => $stasiunUmbulan->id,
            'supervisor_id' => $spvUmbulan->id, //Lapor ke SPV-nya adalah SPV Umbulan
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);


        // --- STASIUN Booster-M ---
        $spvBooster = User::create([
            'nip' => '210',
            'name' => 'SPV_Booster-M',
            'email' => 'spv.booster@meta.com',
            'role_id' => $roleSpv->id,
            'tipe_id' => $operator->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunBooster->id,
            'manager_id' => $manager->id, // Lapor ke Manager yang sama
            'password' => Hash::make('password123'),
        ]);

        $karyawanBooster = User::create([
            'nip' => '220',
            'name' => 'Operator_Booster-M',
            'email' => 'operator.booster@meta.com',
            'role_id' => $roleKaryawan->id,
            'tipe_id' => $operator->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunBooster->id,
            'supervisor_id' => $spvBooster->id, //Lapor ke SPV-nya adalah SPV Booster
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        $karyawanBooster = User::create([
            'nip' => '221',
            'name' => 'Maintanance_Booster-M',
            'email' => 'maintanance.booster@meta.com',
            'role_id' => $roleKaryawan->id,
            'tipe_id' => $maintanance->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunBooster->id,
            'supervisor_id' => $spvBooster->id, //Lapor ke SPV-nya adalah SPV Booster
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        $karyawanBooster = User::create([
            'nip' => '222',
            'name' => 'HSE_Booster-M',
            'email' => 'hse.booster@meta.com',
            'role_id' => $roleKaryawan->id,
            'tipe_id' => $safety->id,
            'gender_id' => $wanita->id,
            'station_id' => $stasiunBooster->id,
            'supervisor_id' => $spvBooster->id, //Lapor ke SPV-nya adalah SPV Booster
            'manager_id' => $manager->id,
            'password' => Hash::make('password123'),
        ]);

        // ==========================================
        // 6. ISI DATA SALDO CUTI OTOMATIS
        // ==========================================
        // Berikan saldo cuti tahunan 2026 untuk kedua karyawan tersebut

        // semua ID user
        $userIds = User::orderBy('id', 'asc')->pluck('id');

        // semua ID jenis cuti
        $jenisCutiIds = [
            $cutiTahunan->id,
            $cutiSakit->id,
            $cutiMelahirkan->id
        ];

        // Looping
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            foreach ($jenisCutiIds as $jenisCutiId) {
                // Cek Gender
                // if ($jenisCutiId == $cutiMelahirkan->id && !in_array(strtolower($user->gender), ['wanita'])) {
                //     continue;
                // }
                SaldoCuti::create([
                    'user_id'       => $userId,
                    'jenis_cuti_id' => $jenisCutiId,
                    'sisa_saldo'    => 12,
                    'tahun'         => 2026
                ]);
            }
        }
    }
}
