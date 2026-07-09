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
        Schema::create('pengajuan_cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('alasan_pembelian')->nullable();
            $table->string('receiving_account')->nullable();
            $table->enum('status_supervisor', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_manager', ['pending', 'approved', 'rejected'])->default('pending');
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
        Schema::dropIfExists('pengajuan_cars');
    }
};
