<?php

namespace Database\Seeders;

use App\Models\Cuti\JenisCuti;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Cuti\SaldoCuti;
use App\Models\Cuti\SubCuti;
use App\Models\User\Gender;
use App\Models\User\Jobdesk;
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

        // A. Stasiun & Kantor Utama (Penempatan Karyawan)
        $stUmbulan = Station::updateOrCreate(['kode_stasiun' => 'UMBULAN'], [
            'name' => 'Stasiun Umbulan', 'type' => 'stasiun',
            'latitude' => -7.7572565, 'longitude' => 112.9314949, 'radius_meters' => 1000,
        ]);
        $stBooster = Station::updateOrCreate(['kode_stasiun' => 'BOOSTER_M'], [
            'name' => 'Booster-M', 'type' => 'stasiun',
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

        $mainStations = [$stUmbulan, $stBooster, $stSurabaya, $stJakarta];

        // B. Rumah Meter / RM (18 Lokasi Service Murni)
        $listRumahMeter = [
            ['kode' => 'RM_00', 'name' => '00. Umbulan Out', 'lat' => -7.7570, 'long' => 112.9310],
            ['kode' => 'RM_01', 'name' => '01. Winongan', 'lat' => -7.7210, 'long' => 112.9520],
            ['kode' => 'RM_02', 'name' => '02. Pohjentrek', 'lat' => -7.6710, 'long' => 112.8910],
            ['kode' => 'RM_03', 'name' => '03. Pleret', 'lat' => -7.6510, 'long' => 112.8810],
            ['kode' => 'RM_04', 'name' => '04. PIER', 'lat' => -7.6010, 'long' => 112.8310],
            ['kode' => 'RM_05', 'name' => '05. Bangil', 'lat' => -7.5910, 'long' => 112.7810],
            ['kode' => 'RM_06', 'name' => '06. Gempol', 'lat' => -7.5810, 'long' => 112.7110],
            ['kode' => 'RM_07', 'name' => '07. Porong PDAB', 'lat' => -7.5410, 'long' => 112.7010],
            ['kode' => 'RM_08', 'name' => '08. Porong PDAM', 'lat' => -7.5380, 'long' => 112.7000],
            ['kode' => 'RM_09', 'name' => '09. Tanggulangin', 'lat' => -7.5010, 'long' => 112.7110],
            ['kode' => 'RM_10', 'name' => '10. Candi', 'lat' => -7.4710, 'long' => 112.7210],
            ['kode' => 'RM_11', 'name' => '11. Sidoarjo', 'lat' => -7.4410, 'long' => 112.7110],
            ['kode' => 'RM_12', 'name' => '12. Buduran', 'lat' => -7.4110, 'long' => 112.7210],
            ['kode' => 'RM_13', 'name' => '13. Gedangan', 'lat' => -7.3810, 'long' => 112.7310],
            ['kode' => 'RM_14', 'name' => '14. Waru', 'lat' => -7.3510, 'long' => 112.7410],
            ['kode' => 'RM_15', 'name' => '15. Wonocolo', 'lat' => -7.3210, 'long' => 112.7410],
            ['kode' => 'RM_16', 'name' => '16. Putat Gedhe', 'lat' => -7.2710, 'long' => 112.6910],
            ['kode' => 'RM_17', 'name' => '17. Alas Malang', 'lat' => -7.2810, 'long' => 112.6810],
            ['kode' => 'RM_18', 'name' => '18. Giri', 'lat' => -7.1610, 'long' => 112.6210],
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
        // 4. MASTER ROLES / JABATAN
        // ==========================================
        $roleAdmin    = Role::create(['role_name' => 'Admin', 'divisi' => 'Operasional', 'level' => 1, 'description' => 'Administrator Utama Sistem ERP']);
        $roleDireksi  = Role::create(['role_name' => 'Direksi', 'divisi' => 'Manajemen', 'level' => 1, 'description' => 'Dewan Direksi / Eksekutif Tertinggi']);
        $roleGM       = Role::create(['role_name' => 'General Manager', 'divisi' => 'Manajemen', 'level' => 1, 'description' => 'Manajemen Puncak Operasional']);
        $roleManager  = Role::create(['role_name' => 'Manager', 'divisi' => 'Manajemen', 'level' => 2, 'description' => 'Kepala Divisi / Sektor']);
        $roleHRD      = Role::create(['role_name' => 'HRD', 'divisi' => 'Manajemen', 'level' => 3, 'description' => 'Pengelolaan Kepegawaian & SDM']);
        $roleHumas    = Role::create(['role_name' => 'Humas', 'divisi' => 'Manajemen', 'level' => 3, 'description' => 'Hubungan Masyarakat & Komunikasi']);
        $roleSpv      = Role::create(['role_name' => 'Supervisor', 'divisi' => 'Operasional', 'level' => 3, 'description' => 'Pengawas Lapangan & Stasiun']);
        $roleStaff    = Role::create(['role_name' => 'Staff', 'divisi' => 'Operasional', 'level' => 4, 'description' => 'Pelaksana Tugas / Operator Lapangan']);

        $roleManager->parent_role_id = $roleGM->id; $roleManager->save();
        $roleHRD->parent_role_id     = $roleManager->id; $roleHRD->save();
        $roleHumas->parent_role_id   = $roleManager->id; $roleHumas->save();
        $roleSpv->parent_role_id     = $roleManager->id; $roleSpv->save();
        $roleStaff->parent_role_id   = $roleSpv->id; $roleStaff->save();
        $roleAdmin->parent_role_id   = $roleSpv->id; $roleAdmin->save();

        // ==========================================
        // 5. MASTER JOBDESK / BIDANG TUGAS
        // ==========================================
        $jobList = [
            'Operator'    => 'Pengoperasian sistem teknis stasiun, valve, dan meteran air',
            'Maintenance' => 'Pemeliharaan instalasi mekanikal, elektrikal, dan instrumen',
            'HSE'         => 'Pengawasan K3, keselamatan kerja, dan lingkungan',
            'Pipeline'    => 'Inspeksi & pemeliharaan jaringan pipa utama transmisi',
            'Finance'     => 'Pengelolaan keuangan, akuntansi, dan pengeluaran operasional',
            'Legal'       => 'Dokumentasi hukum, kontrak kerja, dan perizinan',
            'GS'          => 'General Service, pengelolaan sarana & prasana kantor',
            'Engineering' => 'Perencanaan teknik, evaluasi infrastruktur, dan sistem',
        ];

        foreach ($jobList as $title => $desc) {
            Jobdesk::create(['job_title' => $title, 'description' => $desc]);
        }

        // ==========================================
        // 6. MASTER JENIS CUTI & SUB-CUTI
        // ==========================================

        // Ambil jenis cuti yang sudah ada di database
        $ijinIMP = JenisCuti::where('kode_cuti', 'IMPI')->first();
        $cutiTahunan = JenisCuti::where('kode_cuti', 'CT')->first();

        // Jika jenis cuti tidak ditemukan, buat jenis cuti baru
        if (!$ijinIMP) {
            $ijinIMP = JenisCuti::create([
                'kode_cuti' => 'IMPI',
                'name_cuti' => 'Ijin Meninggalkan Pekerjaan',
                'kuota_default' => null,
                'butuh_surat_dokter' => false,
                'keterangan' => null,
            ]);
        }

        if (!$cutiTahunan) {
            $cutiTahunan = JenisCuti::create([
                'kode_cuti' => 'CT',
                'name_cuti' => 'Cuti',
                'kuota_default' => 12,
                'butuh_surat_dokter' => false,
                'keterangan' => null,
            ]);
        }

        $normalScheduleData = [
            'schedule_type' => 'normal',
            'normal_work_days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
            'normal_check_in' => '07:00',
            'normal_check_out' => '16:00',
        ];

        // ==========================================
        // 7. SEEDING KARYAWAN
        // ==========================================

        $admin = User::create(array_merge([
            'nip' => 'ADM-001', 'name' => 'Admin Sistem', 'email' => 'admin@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleAdmin->id, 'gender_id' => $pria->id,
            'station_id' => $stBooster->id, 'sektor' => 'Operasional', 'job_title' => 'Operator',
            'phone_number' => '081234567890', 'password' => Hash::make('Admin123.'),
        ], $normalScheduleData));

        $direktur = User::create(array_merge([
            'nip' => 'DIR-001', 'name' => 'Ir. Bambang Triyono', 'email' => 'direksi@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleDireksi->id, 'gender_id' => $pria->id,
            'station_id' => $stJakarta->id, 'sektor' => 'manajemen', 'job_title' => 'Engineering',
            'phone_number' => '081100000001', 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $gm = User::create(array_merge([
            'nip' => 'GM-001', 'name' => 'Drs. Hendra Setiawan, M.T.', 'email' => 'gm@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleGM->id, 'gender_id' => $pria->id,
            'station_id' => $stSurabaya->id, 'sektor' => 'manajemen', 'job_title' => 'Engineering',
            'phone_number' => '081100000002', 'supervisor_id' => $direktur->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $mgrOps = User::create(array_merge([
            'nip' => 'MGR-001', 'name' => 'Rahmat Hidayat, S.T.', 'email' => 'mgr.ops@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleManager->id, 'gender_id' => $pria->id,
            'station_id' => $stUmbulan->id, 'sektor' => 'operasional', 'job_title' => 'Operator',
            'phone_number' => '081100000003', 'manager_id' => $gm->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $mgrTeknik = User::create(array_merge([
            'nip' => 'MGR-002', 'name' => 'Agus Priyanto, M.T.', 'email' => 'mgr.teknik@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleManager->id, 'gender_id' => $pria->id,
            'station_id' => $stSurabaya->id, 'sektor' => 'operasional', 'job_title' => 'Engineering',
            'phone_number' => '081100000004', 'manager_id' => $gm->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $mgrHRD = User::create(array_merge([
            'nip' => 'MGR-003', 'name' => 'Siti Rahmawati, S.Psi.', 'email' => 'mgr.hrd@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'role_id' => $roleManager->id, 'gender_id' => $wanita->id,
            'station_id' => $stSurabaya->id, 'sektor' => 'manajemen', 'job_title' => 'Legal',
            'phone_number' => '081100000005', 'manager_id' => $gm->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $spvUmbulan = User::create(array_merge([
            'nip' => 'SPV-001', 'name' => 'Eko Prasetyo', 'email' => 'spv.umbulan@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'phone_number' => '081100000010',
            'role_id' => $roleSpv->id, 'gender_id' => $pria->id, 'station_id' => $stUmbulan->id,
            'sektor' => 'operasional', 'job_title' => 'Operator', 'manager_id' => $mgrOps->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $spvBooster = User::create(array_merge([
            'nip' => 'SPV-002', 'name' => 'Budi Santoso', 'email' => 'spv.booster@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'phone_number' => '081100000011',
            'role_id' => $roleSpv->id, 'gender_id' => $pria->id, 'station_id' => $stBooster->id,
            'sektor' => 'operasional', 'job_title' => 'Maintenance', 'manager_id' => $mgrOps->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $spvSurabaya = User::create(array_merge([
            'nip' => 'SPV-003', 'name' => 'Heri Susanto', 'email' => 'spv.surabaya@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'phone_number' => '081100000012',
            'role_id' => $roleSpv->id, 'gender_id' => $pria->id, 'station_id' => $stSurabaya->id,
            'sektor' => 'operasional', 'job_title' => 'Pipeline', 'manager_id' => $mgrTeknik->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $spvJakarta = User::create(array_merge([
            'nip' => 'SPV-004', 'name' => 'Aris Wijaya', 'email' => 'spv.jakarta@meta.com',
            'email_verified_at' => now(), 'phone_verified_at' => now(), 'phone_number' => '081100000013',
            'role_id' => $roleSpv->id, 'gender_id' => $pria->id, 'station_id' => $stJakarta->id,
            'sektor' => 'operasional', 'job_title' => 'Engineering', 'manager_id' => $mgrTeknik->id, 'password' => Hash::make('Password123.'),
        ], $normalScheduleData));

        $staffKaryawanList = [];
        $namaDummyPria = [
            'Aditya Kuncoro', 'Bayu Nugroho', 'Candra Wijaya', 'Denny Gunawan', 'Edi Sukamto',
            'Farhan Maulana', 'Gilang Ramadhan', 'Hendra Gunawan', 'Irfan Bachdim', 'Joko Susilo',
            'Kurnia Meiga', 'Lukman Hakim', 'M. Ridwan', 'Nanda Pratama', 'Oky Rendy',
            'Panji Petualang', 'Qomaruddin', 'Rendi Febrian', 'Setyo Budi', 'Tomy Sugiarto'
        ];

        $allSupervisors = [$spvUmbulan, $spvBooster, $spvSurabaya, $spvJakarta];
        $jobCategories  = ['Operator', 'Maintenance', 'HSE', 'Pipeline', 'Engineering'];

        $karyawanCounter = 1;
        foreach ($namaDummyPria as $idx => $nama) {
            $assignedStation = $mainStations[$idx % count($mainStations)];
            $assignedSpv     = $allSupervisors[$idx % count($allSupervisors)];
            $jobTitle        = $jobCategories[$idx % count($jobCategories)];
            $rosterStartDate = ($idx % 2 === 0) ? now()->subDays(2) : now()->subDays(1);

            $u = User::create([
                'nip' => sprintf('STF-%03d', $karyawanCounter++),
                'name' => $nama,
                'email' => strtolower(str_replace([' ', '.'], '', $nama)) . '@meta.com',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'phone_number' => sprintf('08129000%04d', $karyawanCounter),
                'role_id' => $roleStaff->id,
                'gender_id' => $pria->id,
                'station_id' => $assignedStation->id,
                'sektor' => 'operasional',
                'job_title' => $jobTitle,
                'supervisor_id' => $assignedSpv->id,
                'manager_id' => $mgrOps->id,
                'schedule_type' => 'roster',
                'roster_start_date' => $rosterStartDate,
                'password' => Hash::make('Password123.'),
            ]);

            $staffKaryawanList[] = $u;
        }

        // ==========================================
        // 8. GENERATE SALDO CUTI OTOMATIS
        // ==========================================
        $allUsers = User::all();
        foreach ($allUsers as $user) {
            // Saldo Cuti Tahunan
            SaldoCuti::firstOrCreate([
                'user_id' => $user->id,
                'jenis_cuti_id' => $cutiTahunan->id,
                'tahun' => 2026,
            ], [
                'sisa_saldo' => 12,
            ]);

            // Saldo IMP (Khusus Wanita untuk Cuti Haid)
            if ($user->gender_id == $wanita->id) {
                SaldoCuti::firstOrCreate([
                    'user_id' => $user->id,
                    'jenis_cuti_id' => $ijinIMP->id,
                    'tahun' => 2026,
                ], [
                    'sisa_saldo' => 2,
                ]);
            }
        }

        // ==========================================
        // 9. SEEDING TRANSACTION RECORDS: CUTI, CAR, & MPR
        // ==========================================

        // A. TRANSAKSI PENGAJUAN CUTI (3 RECORD DUMMY)
        if (count($staffKaryawanList) >= 3) {
            PengajuanCuti::create([
                'user_id' => $staffKaryawanList[0]->id,
                'jenis_cuti_id' => $cutiTahunan->id,
                'tanggal_mulai' => now()->addDays(2)->toDateString(),
                'tanggal_selesai' => now()->addDays(4)->toDateString(),
                'total_hari' => 3,
                'alasan_cuti' => 'Acara keluarga di kampung halaman',
                'status_supervisor' => 'approved',
                'supervisor_id' => $staffKaryawanList[0]->supervisor_id,
                'status_manager' => 'approved',
                'manager_id' => $staffKaryawanList[0]->manager_id,
                'status_akhir' => 'approved',
            ]);

            PengajuanCuti::create([
                'user_id' => $staffKaryawanList[1]->id,
                'jenis_cuti_id' => $ijinIMP->id,
                'sub_cuti_id' => 1,
                'tanggal_mulai' => now()->addDays(1)->toDateString(),
                'tanggal_selesai' => now()->addDays(2)->toDateString(),
                'total_hari' => 2,
                'alasan_cuti' => 'Istirahat karena demam tinggi',
                'status_supervisor' => 'pending',
                'supervisor_id' => $staffKaryawanList[1]->supervisor_id,
                'status_manager' => 'pending',
                'manager_id' => $staffKaryawanList[1]->manager_id,
                'status_akhir' => 'pending',
            ]);

            PengajuanCuti::create([
                'user_id' => $staffKaryawanList[2]->id,
                'jenis_cuti_id' => $cutiTahunan->id,
                'tanggal_mulai' => now()->addDays(5)->toDateString(),
                'tanggal_selesai' => now()->addDays(7)->toDateString(),
                'total_hari' => 3,
                'alasan_cuti' => 'Liburan akhir bulan',
                'status_supervisor' => 'rejected',
                'supervisor_id' => $staffKaryawanList[2]->supervisor_id,
                'status_manager' => 'rejected',
                'manager_id' => $staffKaryawanList[2]->manager_id,
                'status_akhir' => 'rejected',
                'catatan_penolakan' => 'Jadwal shift stasiun sedang padat maintenance.',
            ]);
        }

        // B. TRANSAKSI PENGAJUAN CAR (CASH ADVANCE REQUEST)
        if (count($staffKaryawanList) >= 2) {
            $carId1 = DB::table('pengajuan_cars')->insertGetId([
                'user_id' => $staffKaryawanList[3]->id,
                'alasan_pembelian' => 'Pembelian sparepart & oli darurat stasiun booster',
                'receiving_account' => 'BCA - 8220192831 - Aditya',
                'status_supervisor' => 'approved',
                'supervisor_id' => $staffKaryawanList[3]->supervisor_id,
                'status_manager' => 'approved',
                'manager_id' => $staffKaryawanList[3]->manager_id,
                'status_akhir' => 'approved',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('pengajuan_car_details')->insert([
                [
                    'pengajuan_car_id' => $carId1,
                    'nama_barang' => 'Oli Mesin Pompa High Temp',
                    'jumlah' => 2,
                    'estimasi_harga' => 450000.00,
                    'total_harga' => 900000.00,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'pengajuan_car_id' => $carId1,
                    'nama_barang' => 'Seal Valve 4 Inch',
                    'jumlah' => 5,
                    'estimasi_harga' => 120000.00,
                    'total_harga' => 600000.00,
                    'created_at' => now(), 'updated_at' => now(),
                ]
            ]);

            $carId2 = DB::table('pengajuan_cars')->insertGetId([
                'user_id' => $staffKaryawanList[4]->id,
                'alasan_pembelian' => 'Pengadaan konsumsi & alat kebersihan tim operasional',
                'receiving_account' => 'Mandiri - 14200192812 - Bayu',
                'status_supervisor' => 'pending',
                'supervisor_id' => $staffKaryawanList[4]->supervisor_id,
                'status_manager' => 'pending',
                'manager_id' => $staffKaryawanList[4]->manager_id,
                'status_akhir' => 'pending',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('pengajuan_car_details')->insert([
                [
                    'pengajuan_car_id' => $carId2,
                    'nama_barang' => 'Paket APD & Sarung Tangan Karet',
                    'jumlah' => 10,
                    'estimasi_harga' => 35000.00,
                    'total_harga' => 350000.00,
                    'created_at' => now(), 'updated_at' => now(),
                ]
            ]);
        }

        // C. TRANSAKSI PENGAJUAN MPR (MATERIAL PURCHASE REQUEST)
        if (count($staffKaryawanList) >= 2) {
            $mprId1 = DB::table('pengajuan_mprs')->insertGetId([
                'user_id' => $staffKaryawanList[5]->id,
                'nomor_mpr' => 'MPR/' . date('Y/m') . '/001',
                'tanggal_pengajuan' => now()->toDateString(),
                'keperluan_urgensi' => 'Perbaikan kebocoran sambungan pipa pipa utama',
                'status_supervisor' => 'approved',
                'supervisor_id' => $staffKaryawanList[5]->supervisor_id,
                'status_manager' => 'approved',
                'manager_id' => $staffKaryawanList[5]->manager_id,
                'status_akhir' => 'approved',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('pengajuan_mpr_details')->insert([
                [
                    'pengajuan_mpr_id' => $mprId1,
                    'nama_barang' => 'Pipa Baja Seamless 6 Inch',
                    'jumlah' => 4,
                    'satuan' => 'Batang',
                    'estimasi_harga' => 2500000.00,
                    'keterangan_item' => 'Urgensi penambalan pipa jalur transmisi',
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'pengajuan_mpr_id' => $mprId1,
                    'nama_barang' => 'Flange Joint Set Heavy Duty',
                    'jumlah' => 8,
                    'satuan' => 'Set',
                    'estimasi_harga' => 350000.00,
                    'keterangan_item' => 'Lengkap dengan baut galvanis',
                    'created_at' => now(), 'updated_at' => now(),
                ]
            ]);

            $mprId2 = DB::table('pengajuan_mprs')->insertGetId([
                'user_id' => $staffKaryawanList[6]->id,
                'nomor_mpr' => 'MPR/' . date('Y/m') . '/002',
                'tanggal_pengajuan' => now()->toDateString(),
                'keperluan_urgensi' => 'Peremajaan instrumen sensor tekanan air stasiun',
                'status_supervisor' => 'pending',
                'supervisor_id' => $staffKaryawanList[6]->supervisor_id,
                'status_manager' => 'pending',
                'manager_id' => $staffKaryawanList[6]->manager_id,
                'status_akhir' => 'pending',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('pengajuan_mpr_details')->insert([
                [
                    'pengajuan_mpr_id' => $mprId2,
                    'nama_barang' => 'Pressure Transmitter Digital 0-16 Bar',
                    'jumlah' => 2,
                    'satuan' => 'Unit',
                    'estimasi_harga' => 4200000.00,
                    'keterangan_item' => 'Kalibrasi standar industri',
                    'created_at' => now(), 'updated_at' => now(),
                ]
            ]);
        }
    }
}
