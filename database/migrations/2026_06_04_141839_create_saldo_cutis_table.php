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
        Schema::create('saldo_cutis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jenis_cuti_id')->constrained('jenis_cutis')->onDelete('cascade');
            $table->integer('kuota_awal')->default(12);
            $table->integer('sisa_saldo');
            $table->integer('tahun');
            $table->integer('bulan')->nullable();
            $table->timestamps();

            // Disesuaikan agar unik per user, jenis cuti, dan TAHUN
            $table->unique(['user_id', 'jenis_cuti_id', 'tahun'], 'user_jenis_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldo_cutis');
    }
};
