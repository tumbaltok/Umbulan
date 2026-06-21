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

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // DATA MASTER ROLES
        $roleAdmin = Role::create(['role_name' => 'Admin']);
        $roleManager = Role::create(['role_name' => 'Manager']);
        $roleSpv = Role::create(['role_name' => 'Supervisor']);
        $roleKaryawan = Role::create(['role_name' => 'Karyawan']);

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
        // ONLY 4 UTAMA JENIS CUTI & SUB-CUTI (KETERANGAN)
        // ==========================================

        // 1. Ijin Meninggalkan Pekerjaan (Membawahi ijin-ijin khusus tanpa potong cuti)
        $ijinMeninggalkanPekerjaan = JenisCuti::create([
            'name_cuti'          => 'Ijin Meninggalkan Pekerjaan',
            'kuota_default'      => null,
            'butuh_surat_dokter' => false,
            'keterangan'         => null // Sudah tidak butuh kolom JSON lagi karena beralih ke tabel
        ]);

        $dataSubCuti = [
            ['nama' => 'Sakit', 'durasi' => null, 'ket' => 'Tidak memotong kuota tahunan jika melampirkan surat dokter'],
            ['nama' => 'Haid', 'durasi' => 2, 'ket' => 'Tidak memotong kuota tahunan (Khusus Wanita)'],
            ['nama' => 'Pernikahan Karyawan', 'durasi' => 3, 'ket' => 'Hari Kerja'],
            ['nama' => 'Istri Karyawan Melahirkan', 'durasi' => 3, 'ket' => 'Hari Kerja (Khusus Pria)'],
            ['nama' => 'Kematian Suami/Istri/Anak/Orang Tua/Mertua', 'durasi' => 3, 'ket' => 'Hari Kerja'],
            ['nama' => 'Kematian Kakak/Adik Karyawan', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Pernikahan Anak/Kakak/Adik Karyawan', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Khitanan Anak Karyawan', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Pembaptisan Anak Karyawan', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Kematian Tanggungan Tinggal di Rumah Karyawan', 'durasi' => 2, 'ket' => 'Hari Kerja'],
            ['nama' => 'Karyawan Pindah Rumah', 'durasi' => 2, 'ket' => 'Hari Kerja'],
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

        // 3. Cuti Melahirkan (Membawahi bersalin & gugur kandungan khusus wanita)
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
                'jenis_cuti_id'       => $cutiMelahirkan->id, // Mengarah ke ID Cuti Melahirkan
                'nama_sub_cuti'       => $sub['nama'],
                'durasi_default'      => $sub['durasi'],
                'keterangan_opsional' => $sub['ket']
            ]);
        }

        // 4. Cuti (Membawahi Cuti Tahunan umum, Cuti Haid, dan Cuti Ibadah)
        $cutiTahunan = JenisCuti::create([
            'name_cuti' => 'Cuti',
            'kuota_default' => 12, // Slot utama 12 hari dalam setahun
            'butuh_surat_dokter' => false,
            'keterangan' => []
        ]);


        // ==========================================
        // DATA USERS
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
            'name' => 'SPV  Umbulan',
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

        // Karyawan Wanita (Untuk tes validasi cuti khusus wanita)
        $karyawanWanita = User::create([
            'nip' => '223',
            'name' => 'Karyawan Wanita',
            'email' => 'karyawan@meta.com',
            'role_id' => $roleKaryawan->id,
            'gender_id' => $wanita->id,
            'station_id' => $stasiunBooster->id,
            'password' => Hash::make('karyawan123'),
        ]);


        // ==========================================
        // ISI DATA SALDO CUTI UTOMATIS (TAHUN 2026)
        // ==========================================

        $userIds = User::orderBy('id', 'asc')->pluck('id');

        // Menyiapkan mapping alokasi jatah default 4 jenis cuti utama
        $jenisCutiSaldos = [
            ['id' => $cutiTahunan->id, 'saldo' => 12], // Default kuota cuti tahunan
            ['id' => $ijinMeninggalkanPekerjaan->id, 'saldo' => 0], // Diisi berdasarkan case pengajuan khusus
            ['id' => $cutiFamilyVisit->id, 'saldo' => 0],
            ['id' => $cutiMelahirkan->id, 'saldo' => 45], // Default 1,5 bulan perlindungan melahirkan
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            foreach ($jenisCutiSaldos as $cutiData) {
                $genderUser = strtolower($user->gender->name ?? '');

                // 🌟 PROTEKSI GENDER: Cuti Melahirkan hanya dimasukkan saldonya jika user berjenis kelamin Wanita
                if ($cutiData['id'] == $cutiMelahirkan->id) {
                    if ($genderUser !== 'wanita') {
                        continue; // Lewati pemberian saldo melahirkan jika user adalah pria
                    }
                }

                SaldoCuti::create([
                    'user_id'       => $userId,
                    'jenis_cuti_id' => $cutiData['id'],
                    'sisa_saldo'    => $cutiData['saldo'],
                    'tahun'         => 2026
                ]);
            }
        }
    }
}
