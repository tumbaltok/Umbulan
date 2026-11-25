<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('jenis_cutis')->onDelete('cascade');
            $table->string('kode_cuti')->nullable()->unique();
            $table->string('name_cuti');
            $table->integer('kuota_default')->nullable();
            $table->integer('durasi_default')->nullable();
            $table->boolean('butuh_surat_dokter')->default(false);
            $table->boolean('apakah_wajib_dokumen')->default(false);
            $table->text('keterangan')->nullable();
            $table->string('keterangan_opsional')->nullable();
            $table->timestamps();
        });

        // 1. Data Induk Jenis Cuti (parent_id = null)
        DB::table('jenis_cutis')->insert([
            [
                'id' => 1,
                'parent_id' => null,
                'kode_cuti' => 'IMPI',
                'name_cuti' => 'Ijin Meninggalkan Pekerjaan',
                'kuota_default' => null,
                'durasi_default' => null,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'parent_id' => null,
                'kode_cuti' => 'CFV',
                'name_cuti' => 'Cuti Family Visit/ Penugasan Sementara per 3 bulan',
                'kuota_default' => 0,
                'durasi_default' => null,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'parent_id' => null,
                'kode_cuti' => 'CM',
                'name_cuti' => 'Cuti Melahirkan',
                'kuota_default' => 45,
                'durasi_default' => null,
                'butuh_surat_dokter' => true,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'parent_id' => null,
                'kode_cuti' => 'CT',
                'name_cuti' => 'Cuti',
                'kuota_default' => 12,
                'durasi_default' => null,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // 2. Data Sub-Cuti (parent_id = 1 atau 3)
        DB::table('jenis_cutis')->insert([
            [
                'id' => 5,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Sakit',
                'kuota_default' => null,
                'durasi_default' => null,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Tidak memotong kuota tahunan jika melampirkan surat keterangan dokter',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Haid',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Kuota maks 2 hari per bulan (Khusus Wanita)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Pernikahan',
                'kuota_default' => null,
                'durasi_default' => 3,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Istri Melahirkan',
                'kuota_default' => null,
                'durasi_default' => 3,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja (Khusus Pria)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Kematian Suami/Istri/Anak/Orang Tua/Mertua',
                'kuota_default' => null,
                'durasi_default' => 3,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Kematian Kakak/Adik',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Pernikahan Anak/Kakak/Adik',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Khitanan Anak',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Pembaptisan Anak',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Kematian Tanggungan Tinggal di Rumah',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 15,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Pindah Rumah',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 16,
                'parent_id' => 1,
                'kode_cuti' => null,
                'name_cuti' => 'Bencana Alam',
                'kuota_default' => null,
                'durasi_default' => 2,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Hari Kerja',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 17,
                'parent_id' => 3,
                'kode_cuti' => null,
                'name_cuti' => 'Cuti Ibadah Haji/Umroh',
                'kuota_default' => null,
                'durasi_default' => null,
                'butuh_surat_dokter' => false,
                'apakah_wajib_dokumen' => false,
                'keterangan' => null,
                'keterangan_opsional' => 'Umroh maks 2 tahun sekali - Tidak memotong kuota tahunan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 18,
                'parent_id' => 3,
                'kode_cuti' => null,
                'name_cuti' => 'Istirahat Bersalin',
                'kuota_default' => 45,
                'durasi_default' => 45,
                'butuh_surat_dokter' => true,
                'apakah_wajib_dokumen' => true,
                'keterangan' => null,
                'keterangan_opsional' => '1,5 Bulan sebelum/sesudah melahirkan',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 19,
                'parent_id' => 3,
                'kode_cuti' => null,
                'name_cuti' => 'Istirahat Gugur Kandungan',
                'kuota_default' => 45,
                'durasi_default' => 45,
                'butuh_surat_dokter' => true,
                'apakah_wajib_dokumen' => true,
                'keterangan' => null,
                'keterangan_opsional' => '1,5 Bulan sesuai surat keterangan dokter',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_cutis');
    }
};
