<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Header Pengajuan MPR
        Schema::create('pengajuan_mprs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nomor_mpr')->unique(); // Contoh: MPR/2026/08/001
            $table->date('tanggal_pengajuan');
            $table->text('keperluan_urgensi');
            $table->string('dokumen_pendukung')->nullable();
            
            // Status Persetujuan Bertingkat & ID Approver (Untuk TTD Otomatis)
            $table->enum('status_supervisor', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status_manager', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();
            
            $table->timestamps();
        });

        // 2. Tabel Detail Material/Barang MPR
        Schema::create('pengajuan_mpr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_mpr_id')->constrained('pengajuan_mprs')->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('jumlah');
            $table->string('satuan'); // Pcs, Unit, Box, Meter, dll
            $table->decimal('estimasi_harga', 15, 2)->default(0);
            $table->text('keterangan_item')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_mpr_details');
        Schema::dropIfExists('pengajuan_mprs');
    }
};