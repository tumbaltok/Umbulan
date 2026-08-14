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
            $table->string('kode_cuti')->unique();
            $table->string('name_cuti');
            $table->integer('kuota_default')->nullable();
            $table->boolean('butuh_surat_dokter')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // Menambahkan data awal
        DB::table('jenis_cutis')->insert([
            [
                'kode_cuti' => 'IMPI',
                'name_cuti' => 'Ijin Meninggalkan Pekerjaan',
                'kuota_default' => null,
                'butuh_surat_dokter' => false,
                'keterangan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_cuti' => 'CFV',
                'name_cuti' => 'Cuti Family Visit/ Penugasan Sementara per 3 bulan',
                'kuota_default' => 0,
                'butuh_surat_dokter' => false,
                'keterangan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_cuti' => 'CM',
                'name_cuti' => 'Cuti Melahirkan',
                'kuota_default' => 45,
                'butuh_surat_dokter' => true,
                'keterangan' => null,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'kode_cuti' => 'CT',
                'name_cuti' => 'Cuti',
                'kuota_default' => 12,
                'butuh_surat_dokter' => false,
                'keterangan' => null,
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
        Schema::dropIfExists('jenis_cutis');
    }
};
