<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_mprs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nomor_mpr')->unique();
            $table->enum('priority', ['Normal', 'Urgent', 'Emergency'])->default('Normal');
            $table->string('department')->default('Operation');
            $table->string('delivery_point')->default('Site Umbulan');
            $table->date('latest_mpr_date')->nullable();
            $table->date('tanggal_pengajuan');
            $table->text('keperluan_urgensi');
            $table->string('dokumen_pendukung')->nullable();
            $table->enum('status_tahap_1', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approver_tahap_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_tahap_2', ['pending', 'approved', 'rejected', 'not_required'])->default('pending');
            $table->foreignId('approver_tahap_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pengajuan_mpr_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_mpr_id')->constrained('pengajuan_mprs')->onDelete('cascade');
            $table->string('nama_barang');
            $table->text('keterangan_item')->nullable();
            $table->integer('jumlah');
            $table->string('satuan');
            $table->decimal('estimasi_harga', 15, 2)->default(0)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_mpr_details');
        Schema::dropIfExists('pengajuan_mprs');
    }
};
