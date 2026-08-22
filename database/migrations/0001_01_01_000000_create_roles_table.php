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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_name');
            $table->integer('level')->default(1);
            $table->text('description')->nullable();
            $table->foreignId('parent_role_id')
                ->nullable()
                ->constrained('roles')
                ->nullOnDelete();
            $table->json('approval_rules')->nullable();
            $table->timestamps();
            $table->unique(['role_name', 'divisi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
