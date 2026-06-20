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
        Schema::create('pengajuan_cutis', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('jenis_cuti_id');
            $table->foreignId('sub_cuti_id')->nullable()->constrained('sub_cutis')->onDelete('set null');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_hari');
            $table->text('alasan_cuti')->nullable()->change();
            $table->string('dokumen_pendukung')->nullable();
            // Alur Persetujuan Bertingkat
            $table->enum('status_supervisor', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_manager', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            // Alasan jika cuti ditolak atasan
            $table->text('catatan_penolakan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cutis');
    }
};
