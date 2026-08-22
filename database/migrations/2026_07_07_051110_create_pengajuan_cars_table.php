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
        // 1. Tabel Header Pengajuan CAR
        Schema::create('pengajuan_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('alasan_pembelian')->nullable();
            $table->string('receiving_account')->nullable();

            // Status Persetujuan Bertingkat & ID Approver
            $table->enum('status_supervisor', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status_manager', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();

            $table->timestamps();
        });

        // 2. Tabel Detail Rincian Barang CAR (Auto-Delete saat Header Dihapus)
        Schema::create('pengajuan_car_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_car_id')->constrained('pengajuan_cars')->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('jumlah');
            $table->string('satuan')->default('PCS');
            $table->decimal('estimasi_harga', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->string('dokumen_nota_or_proposal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_car_details');
        Schema::dropIfExists('pengajuan_cars');
    }
};
