<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Gender;
use App\Models\JenisCuti;
use App\Models\Station;
use Illuminate\Support\Facades\Hash;
use App\Models\SaldoCuti;
use App\Models\SubCuti;
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
        // OTOMATIS BERSIHKAN & BUAT ULANG PENYIMPANAN FILE
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
        // DATA MASTER ROLES (Hanya Role Admin & Staff/Lainnya jika dibutuhkan)
        // ==========================================
        $roleAdmin   = Role::create(['role_name' => 'Admin', 'divisi' => 'Manajemen', 'level' => 1, 'description' => 'Akses penuh ke seluruh sistem ERP']);
        $roleManager = Role::create(['role_name' => 'Manager', 'divisi' => 'Manajemen', 'level' => 2, 'description' => 'Persetujuan tingkat manajerial']);
        $roleHRD     = Role::create(['role_name' => 'HRD', 'divisi' => 'Manajemen', 'level' => 3, 'description' => 'Pengelolaan kepegawaian dan data cuti']);
        $roleSpv     = Role::create(['role_name' => 'Supervisor', 'divisi' => 'Operasional', 'level' => 3, 'description' => 'Persetujuan dan pengawasan tingkat stasiun/sektor']);
        $roleStaff   = Role::create(['role_name' => 'Staff', 'divisi' => 'Operasional', 'level' => 4, 'description' => 'Karyawan pelaksana / operator stasiun']);

        // DATA MASTER GENDERS
        $pria   = Gender::create(['name' => 'Pria']);
        $wanita = Gender::create(['name' => 'Wanita']);

        // DATA MASTER STATIONS
        $stasiunUmbulan = Station::updateOrCreate(
            ['kode_stasiun' => 'UMBULAN'],
            [
                'name'          => 'Stasiun Umbulan',
                'type'          => 'stasiun',
                'latitude'      => -7.7572565,
                'longitude'     => 112.9314949,
                'radius_meters' => 1000,
            ]
        );

        // ==========================================
        // DATA MASTER JENIS CUTI & SUB-CUTI (TETAP LENGKAP)
        // ==========================================

        // 1. Ijin Meninggalkan Pekerjaan
        $ijinMeninggalkanPekerjaan = JenisCuti::create([
            'kode_cuti'          => 'IMP',
            'name_cuti'          => 'Ijin Meninggalkan Pekerjaan',
            'kuota_default'      => 12,
            'butuh_surat_dokter' => false,
            'keterangan'         => null
        ]);

        $dataSubCuti = [
            ['nama' => 'Sakit', 'durasi' => null, 'ket' => 'Tidak memotong kuota tahunan jika melampirkan surat dokter'],
            ['nama' => 'Haid', 'durasi' => 2, 'ket' => 'Kuota maks 2 hari per bulan (Khusus Wanita)'],
            ['nama' => 'Pernikahan', 'durasi' => 3, 'ket' => 'Hari Kerja'],
            ['nama' => 'Istri Melahirkan', 'durasi' => 3, 'ket' => 'Hari Kerja (Khusus Pria)'],
            ['nama' => 'Kematian Suami/Istri/Anak/Orang Tua/Mertua', 'durasi' => 3, 'ket' => 'Hari Kerja'],
            ['nama' => 'Kematian Kakak/Adik', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Pernikahan Anak/Kakak/Adik', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Khitanan Anak', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Pembaptisan Anak', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Kematian Tanggungan Tinggal di Rumah', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Pindah Rumah', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Bencana Alam', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Cuti Ibadah Haji/Umroh', 'durasi' => null, 'ket' => 'Umroh maks 2 tahun sekali - Tidak memotong kuota tahunan'],
        ];

        foreach ($dataSubCuti as $sub) {
            SubCuti::create([
                'jenis_cuti_id'       => $ijinMeninggalkanPekerjaan->id,
                'nama_sub_cuti'       => $sub['nama'],
                'durasi_default'      => $sub['durasi'],
                'keterangan_opsional' => $sub['ket']
            ]);
        }

        // 2. Cuti Family Visit
        $cutiFamilyVisit = JenisCuti::create([
            'kode_cuti'          => 'CFV',
            'name_cuti'          => 'Cuti Family Visit/ Penugasan Sementara per 3 bulan',
            'kuota_default'      => 0,
            'butuh_surat_dokter' => false,
            'keterangan'         => null
        ]);

        // 3. Cuti Melahirkan
        $cutiMelahirkan = JenisCuti::create([
            'kode_cuti'          => 'CM',
            'name_cuti'          => 'Cuti Melahirkan',
            'kuota_default'      => 45,
            'butuh_surat_dokter' => true,
            'keterangan'         => null
        ]);

        $subMelahirkan = [
            ['nama' => 'Istirahat Bersalin', 'durasi' => 45, 'ket' => '1,5 Bulan sebelum/sesudah melahirkan'],
            ['nama' => 'Istirahat Gugur Kandungan', 'durasi' => 45, 'ket' => '1,5 Bulan sesuai surat keterangan dokter'],
        ];

        foreach ($subMelahirkan as $sub) {
            SubCuti::create([
                'jenis_cuti_id'       => $cutiMelahirkan->id,
                'nama_sub_cuti'       => $sub['nama'],
                'durasi_default'      => $sub['durasi'],
                'keterangan_opsional' => $sub['ket']
            ]);
        }

        // 4. Cuti Tahunan Utama
        $cutiTahunan = JenisCuti::create([
            'kode_cuti'          => 'CT',
            'name_cuti'          => 'Cuti',
            'kuota_default'      => 12,
            'butuh_surat_dokter' => false,
            'keterangan'         => null
        ]);

        // ==========================================
        // DATA SEEDING USERS (HANYA AKUN ADMIN)
        // ==========================================

        $admin = User::create([
            'nip'               => '000',
            'name'              => 'Admin Sistem',
            'email'             => 'admin@meta.com',
            'email_verified_at' => now(),
            'role_id'           => $roleAdmin->id,
            'gender_id'         => $pria->id,
            'station_id'        => $stasiunUmbulan->id,
            'sektor'            => 'operasional',
            'job_title'         => 'System Administrator',
            'schedule_type'     => 'roster',
            'roster_start_date'  => now(),
            'normal_work_days'  => '',
            'normal_check_in'   => '',
            'normal_check_out'  => '',
            'phone_number'      => '081234567890',
            'phone_verified_at' => now(),
            'password'          => Hash::make('admin123'),
        ]);

        // ==========================================
        // ISI DATA SALDO CUTI OTOMATIS (HANYA UNTUK ADMIN)
        // ==========================================

        $jenisCutiSaldos = [
            ['id' => $cutiTahunan->id, 'saldo' => 12],
            ['id' => $cutiFamilyVisit->id, 'saldo' => 0],
            ['id' => $cutiMelahirkan->id, 'saldo' => 45],
            ['id' => $ijinMeninggalkanPekerjaan->id, 'saldo' => ($admin->gender_id == $wanita->id) ? 2 : 0],
        ];

        foreach ($jenisCutiSaldos as $cutiData) {
            if ($cutiData['id'] == $cutiMelahirkan->id && $admin->gender_id != $wanita->id) {
                continue;
            }

            SaldoCuti::create([
                'user_id'       => $admin->id,
                'jenis_cuti_id' => $cutiData['id'],
                'sisa_saldo'    => $cutiData['saldo'],
                'tahun'         => 2026
            ]);
        }
    }
}