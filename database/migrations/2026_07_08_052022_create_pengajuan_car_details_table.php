<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_car_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_car_id')->constrained('pengajuan_cars')->onDelete('cascade');
            $table->string('nama_barang');
            $table->integer('jumlah');
            $table->decimal('estimasi_harga', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->string('dokumen_nota_or_proposal')->nullable(); // Menyimpan path nota per item
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_car_details');
    }
};
