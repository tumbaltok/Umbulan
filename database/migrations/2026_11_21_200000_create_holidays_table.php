<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('holiday_name');
            $table->smallInteger('holiday_year')->index();
            $table->boolean('is_cuti_bersama')->default(false);
            $table->enum('source', ['government_sync', 'manual_admin', 'system_baseline'])->default('government_sync');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['holiday_year', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
