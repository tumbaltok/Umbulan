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
        Schema::create('sub_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_cuti_id')->constrained('jenis_cutis')->onDelete('cascade');
            $table->string('nama_sub_cuti');
            $table->integer('durasi_default')->nullable();
            $table->string('keterangan_opsional')->nullable();
            $table->boolean('apakah_wajib_dokumen')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_cutis');
    }
};
