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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('jenis_cuti_id')->constrained('jenis_cutis')->onDelete('cascade');
            $table->foreignId('sub_cuti_id')->nullable()->constrained('sub_cutis')->onDelete('set null');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('total_hari');
            $table->text('alasan_cuti')->nullable();
            $table->string('dokumen_pendukung')->nullable();
            $table->enum('status_tahap_1', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approver_tahap_1_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_tahap_2', ['pending', 'approved', 'rejected', 'not_required'])->default('pending');
            $table->foreignId('approver_tahap_2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status_akhir', ['pending', 'approved', 'rejected'])->default('pending');
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
