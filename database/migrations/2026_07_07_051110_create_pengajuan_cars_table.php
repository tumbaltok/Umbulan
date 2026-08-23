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

            // Jumlah tingkat approval yang dibutuhkan (1 atau 2) berdasarkan role pengaju
            $table->tinyInteger('total_approval_levels')->default(1)->comment('1: Cukup 1 Atasan, 2: Butuh 2 Tingkat Atasan');

            // Persetujuan Tahap 1 (Atasan Langsung)
            $table->enum('status_tahap_1', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approver_tahap_1_id')->nullable()->constrained('users')->nullOnDelete();

            // Persetujuan Tahap 2 (Atasan Lebih Tinggi / Manager)
            // 'not_required' digunakan jika user hanya membutuhkan 1 tahap persetujuan
            $table->enum('status_tahap_2', ['pending', 'approved', 'rejected', 'not_required'])->default('pending');
            $table->foreignId('approver_tahap_2_id')->nullable()->constrained('users')->nullOnDelete();

            // Status Akhir Seluruh Proses Pengajuan
            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();

            $table->timestamps();
        });

        // 2. Tabel Detail Rincian Barang CAR
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
