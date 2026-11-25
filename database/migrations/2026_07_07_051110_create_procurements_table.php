<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe', ['CAR', 'MPR'])->default('CAR')->index();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // CAR Attributes
            $table->string('nomor_car')->nullable();
            $table->text('alasan_pembelian')->nullable();
            $table->text('note_explanation')->nullable();
            $table->string('receiving_account')->nullable();

            // MPR Attributes
            $table->string('nomor_mpr')->nullable()->unique();
            $table->enum('priority', ['Normal', 'Urgent', 'Emergency'])->default('Normal');
            $table->string('department')->default('Operation');
            $table->string('delivery_point')->default('Site Umbulan');
            $table->date('latest_mpr_date')->nullable();
            $table->text('keperluan_urgensi')->nullable();
            $table->string('dokumen_pendukung')->nullable();

            // Common Approval & Audit Attributes
            $table->date('tanggal_pengajuan')->nullable();
            $table->tinyInteger('total_approval_levels')->default(1);
            $table->enum('status_tahap_1', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approver_tahap_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_tahap_2', ['pending', 'approved', 'rejected', 'not_required'])->default('pending');
            $table->foreignId('approver_tahap_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_penolakan')->nullable();
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();

            // Performance Indexes
            $table->index(['tipe', 'user_id', 'status_akhir'], 'idx_procurements_user_status');
            $table->index(['tipe', 'status_tahap_1', 'status_tahap_2', 'status_akhir'], 'idx_procurements_tahap_status');
            $table->index(['tipe', 'approver_tahap_1_id', 'status_tahap_1'], 'idx_procurements_approver1');
            $table->index(['tipe', 'approver_tahap_2_id', 'status_tahap_2'], 'idx_procurements_approver2');
        });

        Schema::create('procurement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained('procurements')->onDelete('cascade');
            $table->string('nama_barang');
            $table->text('keterangan_item')->nullable();
            $table->integer('jumlah');
            $table->string('satuan')->default('PCS');
            $table->decimal('estimasi_harga', 15, 2)->default(0)->nullable();
            $table->decimal('ongkir', 15, 2)->default(0);
            $table->decimal('total_harga', 15, 2)->default(0);
            $table->string('dokumen_nota_or_proposal')->nullable();
            $table->timestamps();

            $table->index('procurement_id', 'idx_procurement_details_procurement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_details');
        Schema::dropIfExists('procurements');
    }
};
