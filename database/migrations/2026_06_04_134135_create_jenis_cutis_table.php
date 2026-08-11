<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jenis_cutis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cuti')->unique(); // Tambahkan kode unik
            $table->string('name_cuti'); // Misal: 'Cuti', 'Cuti Tahunan'
            $table->integer('kuota_default')->default(12); // Default kuota
            $table->boolean('butuh_surat_dokter')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_cutis');
    }
};
