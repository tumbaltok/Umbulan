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

        Storage::disk('public')->makeDirectory('profile_photos');
        Storage::disk('public')->makeDirectory('dokumen_cuti');

        // DATA MASTER ROLES
        $roleAdmin = Role::create(['role_name' => 'Admin']);
        $roleManager = Role::create(['role_name' => 'Manager']);
        $roleSpv = Role::create(['role_name' => 'Supervisor']);
        $roleStaff = Role::create(['role_name' => 'Staff']);

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

        // ==========================================
        // DATA MASTER JENIS CUTI & SUB-CUTI
        // ==========================================

        // 1. Ijin Meninggalkan Pekerjaan (Haid dimasukkan kembali ke sini)
        $ijinMeninggalkanPekerjaan = JenisCuti::create([
            'name_cuti'          => 'Ijin Meninggalkan Pekerjaan',
            'kuota_default'      => null,
            'butuh_surat_dokter' => false,
            'keterangan'         => null
        ]);

        $dataSubCuti = [
            ['nama' => 'Sakit', 'durasi' => null, 'ket' => 'Tidak memotong kuota tahunan jika melampirkan surat dokter'],
            ['nama' => 'Haid', 'durasi' => 2, 'ket' => 'Kuota maks 2 hari per bulan (Khusus Wanita)'], // Kembali ke SubCuti
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
            'name_cuti' => 'Cuti Family Visit/ Penugasan Sementara per 3 bulan',
            'kuota_default' => null,
            'butuh_surat_dokter' => false,
            'keterangan' => null
        ]);

        // 3. Cuti Melahirkan
        $cutiMelahirkan = JenisCuti::create([
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
            'name_cuti' => 'Cuti',
            'kuota_default' => 12,
            'butuh_surat_dokter' => false,
            'keterangan' => null
        ]);


        // ==========================================
        // DATA SEEDING USERS
        // ==========================================

        // Akun ADMIN
        User::create([
            'nip' => '000',
            'name' => 'Admin',
            'email' => 'admin@meta.com',
            'role_id' => $roleAdmin->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'password' => Hash::make('admin123'),
        ]);

        // Akun Manager
        User::create([
            'nip' => '100',
            'name' => 'Manager',
            'email' => 'manager@meta.com',
            'role_id' => $roleManager->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'password' => Hash::make('manager123'),
        ]);

        // SPV Umbulan
        User::create([
            'nip' => '110',
            'name' => 'SPV Umbulan',
            'email' => 'spv.umbulan@meta.com',
            'role_id' => $roleSpv->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunUmbulan->id,
            'password' => Hash::make('supervisor123'),
        ]);

        // SPV Booster
        User::create([
            'nip' => '210',
            'name' => 'SPV Booster-M',
            'email' => 'spv.booster@meta.com',
            'role_id' => $roleSpv->id,
            'gender_id' => $pria->id,
            'station_id' => $stasiunBooster->id,
            'password' => Hash::make('supervisor123'),
        ]);

        // Karyawan Wanita
        User::create([
            'nip' => '223',
            'name' => 'Karyawan Wanita',
            'email' => 'staff@meta.com',
            'email_verified_at' => now(),
            'role_id' => $roleStaff->id,
            'gender_id' => $wanita->id,
            'station_id' => $stasiunBooster->id,
            'password' => Hash::make('staff123'),
        ]);


        // ==========================================
        // ISI DATA SALDO CUTI OTOMATIS (TAHUN 2026)
        // ==========================================

        $daftarUser = User::orderBy('id', 'asc')->get();

        foreach ($daftarUser as $user) {

            // Konfigurasi dinamis saldo awal untuk masing-masing user
            $jenisCutiSaldos = [
                ['id' => $cutiTahunan->id, 'saldo' => 12],
                ['id' => $cutiFamilyVisit->id, 'saldo' => 0],
                ['id' => $cutiMelahirkan->id, 'saldo' => 45],
                // Jika user Wanita, 'Ijin Meninggalkan Pekerjaan' langsung diberi isi saldo awal 2 (untuk kuota Haid bulan ini)
                ['id' => $ijinMeninggalkanPekerjaan->id, 'saldo' => ($user->gender_id == $wanita->id) ? 2 : 0],
            ];

            foreach ($jenisCutiSaldos as $cutiData) {

                // Proteksi: Cuti Melahirkan hanya disuntikkan jika user Wanita
                if ($cutiData['id'] == $cutiMelahirkan->id && $user->gender_id != $wanita->id) {
                    continue;
                }

                SaldoCuti::create([
                    'user_id'       => $user->id,
                    'jenis_cuti_id' => $cutiData['id'],
                    'sisa_saldo'    => $cutiData['saldo'],
                    'tahun'         => 2026
                ]);
            }
        }
    }
}
