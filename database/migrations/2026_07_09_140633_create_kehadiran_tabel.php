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
            $table->string('shift_type'); 
            $table->time('scheduled_in')->nullable();
            $table->time('scheduled_out')->nullable();
            $table->time('check_in')->nullable();
            $table->decimal('check_in_lat', 10, 8)->nullable();
            $table->decimal('check_in_long', 11, 8)->nullable();
            $table->boolean('is_in_radius_check_in')->default(true);
            $table->text('reason_out_of_radius_in')->nullable();
            $table->string('face_photo_in')->nullable();
            $table->time('check_out')->nullable();
            $table->decimal('check_out_lat', 10, 8)->nullable();
            $table->decimal('check_out_long', 11, 8)->nullable();
            $table->boolean('is_in_radius_check_out')->default(true);
            $table->boolean('is_early_checkout')->default(false);
            $table->text('reason_checkout')->nullable();
            $table->string('face_photo_out')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadirans');
    }
};