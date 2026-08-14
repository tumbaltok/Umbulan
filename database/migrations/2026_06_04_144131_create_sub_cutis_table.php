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
        Schema::create('sub_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_cuti_id')->constrained('jenis_cutis')->onDelete('cascade');
            $table->string('nama_sub_cuti');
            $table->integer('durasi_default')->nullable();
            $table->string('keterangan_opsional')->nullable();
            $table->boolean('apakah_wajib_dokumen')->default(false);
            $table->timestamps();
        });

        // Menambahkan data awal
        DB::table('sub_cutis')->insert([
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Sakit',
                'durasi_default' => null,
                'keterangan_opsional' => 'Tidak memotong kuota tahunan jika melampirkan surat keterangan dokter',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Haid',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Kuota maks 2 hari per bulan (Khusus Wanita)',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Pernikahan',
                'durasi_default' => 3,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Istri Melahirkan',
                'durasi_default' => 3,
                'keterangan_opsional' => 'Hari Kerja (Khusus Pria)',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Kematian Suami/Istri/Anak/Orang Tua/Mertua',
                'durasi_default' => 3,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Kematian Kakak/Adik',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Pernikahan Anak/Kakak/Adik',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Khitanan Anak',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Pembaptisan Anak',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Kematian Tanggungan Tinggal di Rumah',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Pindah Rumah',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 1,
                'nama_sub_cuti' => 'Bencana Alam',
                'durasi_default' => 2,
                'keterangan_opsional' => 'Hari Kerja',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 3,
                'nama_sub_cuti' => 'Cuti Ibadah Haji/Umroh',
                'durasi_default' => null,
                'keterangan_opsional' => 'Umroh maks 2 tahun sekali - Tidak memotong kuota tahunan',
                'apakah_wajib_dokumen' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 3,
                'nama_sub_cuti' => 'Istirahat Bersalin',
                'durasi_default' => 45,
                'keterangan_opsional' => '1,5 Bulan sebelum/sesudah melahirkan',
                'apakah_wajib_dokumen' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'jenis_cuti_id' => 3,
                'nama_sub_cuti' => 'Istirahat Gugur Kandungan',
                'durasi_default' => 45,
                'keterangan_opsional' => '1,5 Bulan sesuai surat keterangan dokter',
                'apakah_wajib_dokumen' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_cutis');
    }
};
