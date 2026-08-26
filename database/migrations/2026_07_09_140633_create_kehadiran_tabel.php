<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('shift_type')->default('Normal');
            $table->time('scheduled_in')->nullable();
            $table->time('scheduled_out')->nullable();

            // Presensi Masuk (Check-In)
            $table->time('check_in')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_long', 11, 8)->nullable();
            $table->decimal('check_in_distance', 8, 2)->nullable(); // Jarak ke stasiun (meter)
            $table->boolean('is_in_radius_check_in')->default(true);
            $table->boolean('is_late')->default(false);
            $table->boolean('is_face_verified_in')->default(false);
            $table->text('reason_in')->nullable(); // Alasan terlambat / luar radius
            $table->string('evidence_in')->nullable(); // Path foto / dokumen bukti (Watermarked)

            // Presensi Pulang (Check-Out)
            $table->time('check_out')->nullable();
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_long', 11, 8)->nullable();
            $table->decimal('check_out_distance', 8, 2)->nullable(); // Jarak ke stasiun (meter)
            $table->boolean('is_in_radius_check_out')->default(true);
            $table->boolean('is_early_checkout')->default(false);
            $table->boolean('is_face_verified_out')->default(false);
            $table->text('reason_out')->nullable(); // Alasan pulang awal / luar radius
            $table->string('evidence_out')->nullable(); // Path foto / dokumen bukti (Watermarked)

            // Status Keseluruhan
            $table->enum('status', ['Hadir', 'Terlambat', 'Izin', 'Alpha', 'Libur'])->default('Hadir');

            // Kolom Legacy (Nullable untuk keamanan backward-compatibility)
            $table->text('reason_out_of_radius_in')->nullable();
            $table->text('reason_checkout')->nullable();
            $table->string('face_photo_in')->nullable();
            $table->string('face_photo_out')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadirans');
    }
};
